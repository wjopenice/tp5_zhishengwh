<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/24
 * Time: 14:39
 */
namespace app\pay\controller;
use app\common\controller\Pay;
use lib\Excel;
class Profit extends Pay
{
    public function index(){
        //已提现总金额
        $all_with = db('profit_withdraw')->where("status=4")->value("sum(money)");
        $this->assign('all_with',$all_with ? $all_with : '0.00');
        //当日提现金额
        $now_time = strtotime(date("Y-m-d",time()) . " 00:00:00");
        $with = db('profit_withdraw')->where("status=4" . " and ctime > ".$now_time)->value("sum(money)");

        //佣金总额
        $commission_data = db('profit_withdraw')->where("status=4")->select();
        $commission = 0;
        foreach ($commission_data as $key=>$val){
            $commission += $val['actual_money'];
        }
        //提现中金额
        $is_with = db('profit_withdraw')->where("status=0 or status=2 or status=3")->value("sum(money)");

        $this->assign('with',$with ? $with : '0.00');
        $this->assign('commission',$commission ? $commission : '0.00');
        $this->assign('is_with',$is_with ? $is_with : '0.00');
        return view("index");
    }

    //获取佣金提现记录
    public function getdata(){
        if (!request()->isGet()) {
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        } else {

            $page  = input('get.page');
            $limit = input('get.limit');
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询
            $sta       = strtotime(input('get.start_time'));
            $end       = strtotime(input('get.first_time'));
            $account = input('get.account');
            $status = input('get.status');
            $where = 1;

            if (!empty($sta) && !empty($end)) {
                $where .= ' and (w.ctime > ' . $sta . ' and w.ctime <' . $end . ")";
            }else{
                if(!empty($sta) && empty($end)){
                    $where .= ' and w.ctime > ' . $sta;
                }
                if(empty($sta) && !empty($end)){
                    $where .= ' and w.ctime <' . $end;
                }
            }
            if($account){
                $where .= " and p.account='".$account."'";
            }
            if($status != ''){
                $where .= " and w.status=".$status;
            }
            //---------------END--------------//
            $data  = db('profit_withdraw')
                ->alias('w')
                ->field('w.*,p.account')
                ->join('promote p','p.id=w.promote_id')
                ->join('promote_level p_l', 'p.level_id = p_l.id', 'left')
                ->where($where)
                ->order('w.id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('profit_withdraw')
                ->alias('w')
                ->field('w.*,p.account')
                ->join('promote p','p.id=w.promote_id')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']           = $v['id'];
                    $v['account']      = $v['account'];
                    $v['money'] = $v['money'];
                    $v['revenue'] = $v['rate'];
                    $v['actual_money'] = $v['actual_money'];
                    $v['bank_id']    = getIdBankName($v['bank_id']);
                    $v['status']  = getWithStatus($v['status']);
                    $v['remark']       = $v['remark'];
                    $v['ctime']  = $v['ctime'] ? date('Y-m-d H:i:s', $v['ctime']) : '';
                    $v['audit_time']  = $v['audit_time'] ? date('Y-m-d H:i:s', $v['audit_time']) : '';
                    $v['key']          = $k + 1;
                }
                return [
                    'code'  => 0,
                    'msg'   => '',
                    'count' => $count,
                    'data'  => $data
                ];
            } else {
                return [
                    'code' => 1,
                    'msg'  => '没有数据'
                ];
            }
        }
    }

    /**
     * status [状态转换]
     *
     * author dear
     * @param $val
     * @return string
     */
    private function status($val)
    {
        switch ($val) {
            case 0;
                $res = '待审核';
                break;
            case 1;
                $res = '审核不通过';
                break;
            case 2;
                $res = '审核通过';
                break;
            case 3;
                $res = '打款中';
                break;
            case 4;
                $res = '提现完成';
                break;
        }
        return $res;
    }

    /**
     * detail [提现管理详情]
     *
     * author dear
     * @return mixed
     */
    public function detail()
    {
        $map['w.id'] = input('id');
        $map['w.promote_id'] = session('promote_auth')['id'];
        $field = "p.account,p.nickname,p_l.comm_revenue,w.*,b.bank_name,b.sub_branch,b.bank_number,b.bank_user";
        $info = db('profit_withdraw')
            ->alias('w')
            ->field($field)
            ->join('binding_bank b', 'w.bank_id = b.bank_id', 'left')
            ->join('promote p', 'w.promote_id = p.id', 'left')
            ->join('promote_level p_l', 'p.level_id = p_l.id', 'left')
            ->where($map)
            ->find();

        // 数据处理
        $info['status'] = $this->status($info['status']);
        $info['bank_name'] = getIdBankName($info['bank_id']);
        $info['ctime'] = $info['ctime'] ? date('Y-m-d H:i:s',$info['ctime']) : '';
        $info['audit_time'] = $info['audit_time'] ? date('Y-m-d H:i:s',$info['audit_time']) : '';
        $this->assign('info',$info);
        return view("detail");
    }

    //导出数据
    public function downdata() {
        set_time_limit(0);
        if (!request()->isGet()) {
            exit('非法操作');
        } else {
            $sta       = strtotime(input('get.start_time'));
            $end       = strtotime(input('get.first_time'));
            $account = input('get.account');
            $nickname = input('get.nickname');
            $status = input('get.status');
            $where = 1;

            if (!empty($sta) && !empty($end)) {
                $where .= ' and (a.ctime > ' . $sta . ' and a.ctime <' . $end . ")";
            }else{
                if(!empty($sta) && empty($end)){
                    $where .= ' and a.ctime > ' . $sta;
                }
                if(empty($sta) && !empty($end)){
                    $where .= ' and a.ctime <' . $end;
                }
            }
            if($account){
                $where .= " and p.account='".$account."'";
            }
            if($nickname){
                $where .= " and p.nickname='".$nickname."'";
            }
            if($status != ''){
                $where .= " and a.status=".$status;
            }
            //---------------END--------------//
            $data  = db('profit_withdraw')
                ->alias('w')
                ->field('w.*,p.account')
                ->join('promote p','p.id=w.promote_id')
                ->join('promote_level p_l', 'p.level_id = p_l.id', 'left')
                ->where($where)
                ->order('w.id desc')
                ->select();
            if (!empty($data)) {
                $list = [];
                foreach ($data as $kr => $or) {
                    echo $or['rate'];
                    $o = [
                        $or['account'],
                        $or['money'],
                        $or['rate'],
                        $or['actual_money'],
                        getIdBankName($or['bank_id']),
                        getIdBankNum($or['bank_id']),
                        getWithStatus($or['status']),
                        $or['ctime'] ? date("Y-m-d H:i:s",$or['ctime']) : '',
                        $or['audit_time'] ? date("Y-m-d H:i:s",$or['audit_time']) : '',
                    ];

                    if (!empty($o)) {
                        array_push($list, $o);
                    }
                }
            }
            $fileName = '佣金提现记录' . date('Y-m-d H:i:s', time()) . '.xls';
            $fileHead = [
                '商户号',
                '佣金提现额',
                '提现费率',
                '实际到帐',
                '提现银行',
                '银行卡号',
                '提现状态',
                '发起时间',
                '审核时间',
            ];
            $letter   = [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I'
            ];
            (new Excel())->downExcel($fileName, $fileHead, $list, $letter, 'D');
        }
    }


}