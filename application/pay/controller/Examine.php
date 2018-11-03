<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/3/20
 * Time: 15:42
 */
namespace app\pay\controller;
use app\common\controller\Pay;
use think\Config;
use lib\Excel;
use think\Db;
class Examine extends Pay {

    //提现审核
    public function withauth(){
        if(!request()->isPost()){
            $id = input('get.id');
            $data =  db('auth_withdraw')
                ->alias('a')
                ->field('a.*,b.*,a.status as bstatus,p.account,p.nickname')
                ->join('promote p','p.id=a.promote_id')
                ->join('binding_bank b','b.bank_id=a.bank_id')
                ->where("a.id=".$id)
                ->find();
            $data['bank_name'] = getBankName($data['bank_name']);
            $data['bank_city'] = getBankRegion($data['bank_city']);
            $this->assign('data',$data);
            return view("withauth");
        }else{
            $data = input('post.');
            Db::startTrans();
            try {
                //改变提现状态
                $result = $this->upWithStatus($data['id'], $data['status'], $data['remark'], 1);
                $find = db('auth_withdraw')->where("id=" . $data['id'])->find();
                if ($find['auth_way'] == 0) {
                    if ($find['type'] == 1) {
                        $up_money = 'money';
                    }
                    if ($find['type'] == 2) {
                        $up_money = 't0_money';
                    }
                }
                if ($find['auth_way'] == 1) {
                    $up_money = 'h5_wetch_money';
                }
                if ($find['auth_way'] == 2) {
                    $up_money = 'h5_alipay_money';
                }

                if ($data['status'] == '1') {
                    $upMoney = db('promote')->where("id=" . $data['pid'])->setInc($up_money, $data['money']);
                } else {
                    $upMoney = 1;
                }
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
            }
            if(!empty($result) && !empty($upMoney)){
                return [
                    'code' => 1,
                    'msg'  => '操作完成'
                ];
            }else{
                return [
                    'code' => 0,
                    'msg'  => '操作失败'
                ];
            }
        }
    }

    //佣金提现审核
    public function profitauth(){
        if(!request()->isPost()){
            $id = input('get.id');
            $data =  db('profit_withdraw')
                ->alias('a')
                ->field('a.*,b.*,a.status as bstatus,p.account,p.nickname')
                ->join('promote p','p.id=a.promote_id')
                ->join('binding_bank b','b.bank_id=a.bank_id')
                ->where("a.id=".$id)
                ->find();
            $data['bank_name'] = getBankName($data['bank_name']);
            $data['bank_city'] = getBankRegion($data['bank_city']);

            $this->assign('data',$data);
            return view("profitauth");
        }else{
            $data = input('post.');
            Db::startTrans();
            try{
                //改变提现状态
                $result = $this->upWithStatus($data['id'],$data['status'],$data['remark'],2);

                if($data['status'] == '1'){
                    $upMoney = db('promote')->where("id=".$data['pid'])->setInc('freeze_money',$data['money']);
                }else if($data['status'] == '2'){
                    $upMoney = db('promote')->where("id=".$data['pid'])->setInc('profit_money',$data['actual_money']);
                }else{
                    $upMoney = 1;
                }

                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
            }
            if(!empty($result) && !empty($upMoney)){
                return [
                    'code' => 1,
                    'msg'  => '操作完成'
                ];
            }else{
                return [
                    'code' => 0,
                    'msg'  => '操作失败'
                ];
            }
        }
    }

    //更改提现状态
    public function upwithwtatus($id,$status,$remark,$type){
        if($type == 1){
            $up_data = [
                'status' => $status,
                'remark' => $remark,
                'audit_time' => time()
            ];
            $re = db('auth_withdraw')->where("id=".$id)->update($up_data);
        }
        if($type == 2){
            $up_data = [
                'status' => $status,
                'remark' => $remark,
                'audit_time' => time()
            ];
            $re = db('profit_withdraw')->where("id=".$id)->update($up_data);
        }
        return $re;
    }

    //查询是否有可导出的数据
    public function selectdata(){
        if(!request()->isGet()){
            exit('非法操作');
        }else{
            //TODO::待加入条件查询
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
            $data  = db('auth_withdraw')
                ->alias('a')
                ->field('a.*,p.account,p.nickname')
                ->join('promote p','p.id=a.promote_id')
                ->where($where)
                ->select();
            if (!empty($data)) {
                return [
                    'code'  => 1,
                ];
            } else {
                return [
                    'code' => 0,
                    'msg'  => '没有数据'
                ];
            }
        }
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
            $data  = db('auth_withdraw')
                ->alias('a')
                ->field('a.*,b.bank_id,b.bank_name,a.status as bstatus,p.account,p.nickname')
                ->join('promote p','p.id=a.promote_id')
                ->join('binding_bank b','b.bank_id=a.bank_id')
                ->where($where)
                ->order('a.id desc')
                ->select();
            if (!empty($data)) {
                $list = [];
                foreach ($data as $kr => $or) {
                    $o = [
                        $or['account'],
                        $or['nickname'],
                        $or['money'],
                        $or['money'] * $or['rate'],
                        $or['actual_money'],
                        getBankName($or['bank_name']),
                        getWithStatus($or['bstatus']),
                        $or['ctime'] ? date("Y-m-d H:i:s",$or['ctime']) : '',
                        $or['audit_time'] ? date("Y-m-d H:i:s",$or['audit_time']) : '',
                    ];

                    if (!empty($o)) {
                        array_push($list, $o);
                    }
                }
            }
            $fileName = '提现记录' . date('Y-m-d H:i:s', time()) . '.xls';
            $fileHead = [
                '商户号',
                '商户名称',
                '提现金额',
                '提现手续费',
                '实际到帐',
                '提现银行',
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