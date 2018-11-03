<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/3/19
 * Time: 15:14
 */
namespace app\pay\controller;

use app\common\controller\Admin;
use app\common\controller\Pay;
use think\Config;
use lib\Excel;
class Administration extends Pay{

    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';

    //充值列表
    public function index(){
        //查询当前充值总金额
        $total_money = db('promote_deposit')->where("pay_status=1")->value('sum(pay_amount)');
        $this->assign('total_money',$total_money ? $total_money : '0.00');
        //查询当日充值额和提现额
        $now_time = strtotime(date("Y-m-d",time()) . " 00:00:00");
        //充值额
        $recharge = db('promote_deposit')->where("pay_status=1"." and create_time > ".$now_time)->value("sum(pay_amount)");
        $this->assign('recharge',$recharge ? $recharge : '0.00');

        //结算总金额
        $map['pay_status'] = 1;
        $map['status'] = 1;
        $promote_deposit = db('promote_deposit')->field("sum(pay_amount) pay_amount")->where($map)->find();
        $promote_deposit = $promote_deposit['pay_amount'] ?? 0;
        $this->assign('pay_amount', $promote_deposit);

        // 未结算金额
        $map['pay_status'] = 1;
        $map['status'] = 0;
        $unpromote_deposit = db('promote_deposit')->field("sum(pay_amount) pay_amount")->where($map)->find();
        $unpromote_deposit = $unpromote_deposit['pay_amount'] ?? 0;
        $this->assign('unpay_amount', $unpromote_deposit);

        return view("index");
    }

    //获取充值页统计数据
    public function getcount(){
        if (!request()->isGet()) {
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        } else {

            //TODO::待加入条件查询
            $sta       = strtotime(input('get.start_time'));
            $end       = strtotime(input('get.first_time'));
            $order_number = input('get.order_number');
            $pay_order_number = input('get.pay_order_number');
            $pay_way = input('get.pay_way');
            $pay_status = input('get.pay_status');
            $status  = input('get.status');
            $pay_type = input('get.pay_type');
            $account = input('get.account');
            $where   = 1;

            if (!empty($sta) && !empty($end)) {
                $where .= ' and (pd.create_time > ' . $sta . ' and pd.create_time <' . $end . ")";
            }else{
                if(!empty($sta) && empty($end)){
                    $where .= ' and pd.create_time > ' . $sta;
                }
                if(empty($sta) && !empty($end)){
                    $where .= ' and pd.create_time <' . $end;
                }
            }
            if (!empty($order_number)) {
                $where .= " and pd.order_number='".$order_number."'";
            }
            if (!empty($pay_order_number)) {
                $where .= " and pd.pay_order_number='".$pay_order_number."'";
            }
            if($pay_status != ''){
                $where .= " and pd.pay_status = ".$pay_status;
            }
            if(isset($status) && $status != '2' && $status != null){
                $where .= " and pd.status = ".$status;
            }
            if($pay_way){
                $where .= " and pd.pay_way = " . $pay_way;
            }
            if($pay_type){
                $where .= " and pd.pay_type = " . $pay_type;
            }

            if($account){
                $where .= " and p.account = '" . $account . "'";
                $begintime=strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y'))));
                $time_where = $where." and pd.create_time <=".$begintime;
                $time_data  = db('promote_deposit')
                    ->alias('pd')
                    ->field('sum(pd.pay_amount) time_pay')
                    ->join('promote p','p.id=pd.promote_id')
                    ->where($time_where)
                    ->find();

            }
            //---------------END--------------//
            $data  = db('promote_deposit')
                ->alias('pd')
                ->field('sum(pd.pay_amount) all_pay')
                ->join('promote p','p.id=pd.promote_id')
                ->where($where)
                ->find();

            if (!empty($data)) {
                if($account){
                    return [
                        'code'  => 2,
                        'all_pay'  => $data['all_pay'] ? $data['all_pay'] : '0.00',
                        'time_pay'  => $time_data['time_pay'] ? $time_data['time_pay'] : '0.00'
                    ];
                }else{
                    return [
                        'code'  => 1,
                        'all_pay'  => $data['all_pay'] ? $data['all_pay'] : '0.00'
                    ];
                }

            } else {
                return [
                    'code' => 0,
                    'msg'  => '没有数据'
                ];
            }
        }
    }

    //获取所有充值列表
    public function getrech(){
        if (!request()->isGet()) {
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        } else {

            $page  = input('get.page') ? input('get.page') : 1;
            $limit = input('get.limit') ? input('get.limit') : 10;
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询
            $sta       = strtotime(input('get.start_time'));
            $end       = strtotime(input('get.first_time'));
            $order_number = input('get.order_number');
            $pay_order_number = input('get.pay_order_number');
            $account = input('get.account');
            $pay_way = input('get.pay_way');
            $pay_status = input('get.pay_status');
            $pay_type = input('get.pay_type');
            $where   = 1;

            if (!empty($sta) && !empty($end)) {
                $where .= ' and (pd.create_time > ' . $sta . ' and pd.create_time <' . $end . ")";
            }else{
                if(!empty($sta) && empty($end)){
                    $where .= ' and pd.create_time > ' . $sta;
                }
                if(empty($sta) && !empty($end)){
                    $where .= ' and pd.create_time <' . $end;
                }
            }
            if (!empty($order_number)) {
                $where .= " and pd.order_number='".$order_number."'";
            }
            if(!empty($account)){
                $where .= " and pd.promote_account='".$account."'";
            }
            if (!empty($pay_order_number)) {
                $where .= " and pd.pay_order_number='".$pay_order_number."'";
            }
            if(isset($pay_status) && $pay_status != ''){
                $where .= " and pd.pay_status = ".$pay_status;
            }
            if($pay_way){
                $where .= " and pd.pay_way=" . $pay_way;
            }
            if(input('status') != null){
                $where .= " and pd.status=". input('status');
            }
            if($pay_type){
                $where .= " and pd.pay_type=" . $pay_type;
            }
            //---------------END--------------//
            $data  = db('promote_deposit')
                ->alias('pd')
                ->field('pd.*,p.account,p.id pid,p.level_id')
                ->join('promote p','p.id=pd.promote_id')
                ->where($where)
                ->order('pd.id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('promote_deposit')
                ->alias('pd')
                ->field('pd.*,p.account,p.id')
                ->join('promote p','p.id=pd.promote_id')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => $v) {
                    $v['id']                 = $v['id'];
                    $v['order_number']       =  $v['order_number'];
                    $v['pay_order_number']   = $v['pay_order_number'];
                    $v['promote_account']    = $v['promote_account'];
                    $v['commission']         = $this->getcomm($v['level_id'],$v['pay_amount'],$v['pay_way'],$v['pay_status']);
                    $v['comm_money']         = $this->getcommmoney($v['level_id'],$v['pay_amount'],$v['pay_way'],$v['pay_status']);
                    $v['pay_status']         = getPayStatus($v['pay_status']);
                    $v['pay_way']            = $v['pay_way'] ? getPayType($v['pay_way']) : '';
                    $v['pay_type']           = getPayClass($v['pay_type']);
                    $v['pay_amount']         = number_format($v['pay_amount'],2);
                    $v['pay_source']         = getSubject($v['pay_source']);
                    $v['pay_ip']             = $v['pay_ip'];
                    $v['create_time']        = $v['create_time'] ? date('Y-m-d H:i:s', $v['create_time']) : '';
                    $v['key']                = $k + 1;
                    $v['status']             = $this->getstatus($v['status']);
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

    /*
*获取单笔订单收取的手续费
*/
    public function getcomm($level_id,$amount,$pay_way,$pay_status){
        if($pay_status == 1){
            $level = db('promote_level')->where("id",$level_id)->find();
            if($pay_way == 1 || $pay_way == 2 || $pay_way == 3){
                $commission = number_format($amount * $level['revenue'],2);
            }
            if($pay_way == 4){
                $commission = number_format($amount * $level['h5_wetch_revenue'],2);
            }
            if($pay_way == 5){
                $commission = number_format($amount * $level['h5_alipay_revenue'],2);
            }
        }else{
            $commission = '0.00';
        }

        return $commission;
    }

    /*
*获取单笔订单的结算金额
*/
    public function getcommmoney($level_id,$amount,$pay_way,$pay_status){
        if($pay_status == 1){
            $level = db('promote_level')->where("id",$level_id)->find();
            if($pay_way == 1 || $pay_way == 2 || $pay_way == 3){
                $commission = number_format($amount - $amount * $level['revenue'],2);
            }
            if($pay_way == 4){
                $commission = number_format($amount - $amount * $level['h5_wetch_revenue'],2);
            }
            if($pay_way == 5){
                $commission = number_format($amount - $amount * $level['h5_alipay_revenue'],2);
            }
        }else{
            $commission = '0.00';
        }

        return $commission;
    }

    //查询是否有可导出的数据
    public function selectdata(){
        if(!request()->isGet()){
            exit('非法操作');
        }else{
            $sta       = strtotime(input('get.start_time'));
            $end       = strtotime(input('get.first_time'));
            $order_number = input('get.order_number');
            $pay_order_number = input('get.pay_order_number');
            $account = input('get.account');
            $pay_way = input('get.pay_way');
            $pay_status = input('get.pay_status');
            $pay_type = input('get.pay_type');
            $where   = 1;

            if (!empty($sta) && !empty($end)) {
                $where .= ' and (pd.create_time > ' . $sta . ' and pd.create_time <' . $end . ")";
            }else{
                if(!empty($sta) && empty($end)){
                    $where .= ' and pd.create_time > ' . $sta;
                }
                if(empty($sta) && !empty($end)){
                    $where .= ' and pd.create_time <' . $end;
                }
            }
            if (!empty($order_number)) {
                $where .= " and pd.order_number='".$order_number."'";
            }
            if (!empty($pay_order_number)) {
                $where .= " and pd.pay_order_number='".$pay_order_number."'";
            }
            if(!empty($account)){
                $where .= " and pd.promote_account='".$account."'";
            }
            if(!empty($pay_status) && $pay_status != '2'){
                $where .= " and pd.pay_status = ".$pay_status;
            }
            if($pay_way){
                $where .= " and pd.pay_way = " . $pay_way;
            }
            if($pay_type){
                $where .= " and pd.pay_type=" . $pay_type;
            }
            $data  = db('promote_deposit')
                ->alias('pd')
                ->field('pd.*,p.account,p.id')
                ->join('promote p','p.id=pd.promote_id')
                ->where($where)
                ->select();
            if($data){
                return [
                    'code' => 1,
                ];
            }else{
                return [
                    'code' => 0,
                    'msg'  => '没有数据'
                ];
            }
        }
    }

    //导出数据
    public function downdata() {
        //set_time_limit(0);
        if (!request()->isGet()) {
            exit('非法操作');
        } else {

            $where   = "pd.pay_status = 1";
            $data  = db('promote_deposit')
                ->alias('pd')
                ->field('pd.*,p.account,p.id')
                ->join('promote p','p.id=pd.promote_id')
                ->where($where)
                ->order('pd.id desc')
                ->select();

            if (!empty($data)) {
                $list = [];
                foreach ($data as $kr => $or) {
                    $o = [
                        $or['account'],
                        $or['order_number'] ? $or['order_number'] : '',
                        $or['pay_order_number'],
                        $or['pay_amount'],
                        getPayStatus($or['pay_status']),
                        getPayType($or['pay_way']),
                        getPayClass($or['pay_type']),
                        $or['status'] ? '已结算' : '未结算',
                        date("Y-m-d H:i:s",$or['create_time']),
                    ];

                    if (!empty($o)) {
                        array_push($list, $o);
                    }
                }
            }
            $fileName = '充值记录' . date('Y-m-d H:i:s', time()) . '.xls';
            $fileHead = [
                '商户号',
                '平台订单号',
                '商户订单号',
                '充值金额',
                '充值状态',
                '支付方式',
                '第三方',
                '结算状态',
                '充值时间',
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

    //新导出
    public function downnewdata(){
        set_time_limit(0);
        $fileName = date('Y-m-d H-i-s', time()).".csv";
        $fp = fopen("./data/xls/".$fileName, 'a+');

        // 输出Excel列名信息
        $head = array('商户号','平台订单号','商户订单号','充值金额','充值状态','支付方式','第三方','结算状态',
            '充值时间',);
        foreach ($head as $i => $v) {
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $head[$i] = iconv('utf-8', 'GBK', $v);
        }

        // 将数据通过fputcsv写到文件句柄
        fputcsv($fp, $head);

        // 计数器
        $cnt = 0;
        // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;

        $where   = "pd.pay_status = 1";
        $data  = db('promote_deposit')
            ->alias('pd')
            ->field('pd.*,p.account,p.id pid,p.level_id')
            ->join('promote p','p.id=pd.promote_id')
            ->where($where)
            ->order('pd.id desc')
            ->select();
        if (!empty($data)) {
            $list = [];
            foreach ($data as $kr => $or) {
                $o = [
                    "\t".$or['account']."\t",
                    "\t".$or['order_number'] ? $or['order_number'] : '' ."\t",
                    "\t".$or['pay_order_number']."\t",
                    $or['pay_amount'],
                    "\t".getPayStatus($or['pay_status'])."\t",
                    "\t".getPayType($or['pay_way'])."\t",
                    "\t".getPayClass($or['pay_type'])."\t",
                    "\t".$or['status'] ? '已结算' : '未结算' ."\t",
                    "\t".date("Y-m-d H:i:s",$or['create_time'])."\t",
                ];

                if (!empty($o)) {
                    array_push($list, $o);
                }
            }
        }else{
            return [
                'code' => 0,
                'msg'  => '没有数据'
            ];
            exit;
        }

        foreach($list as $k=>$v){

            $cnt ++;
            if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
                ob_flush();
                flush();
                $cnt = 0;
            }
            //$list[$k] = iconv('utf-8', 'gb2312', $v[$k]);

            foreach ($v as $i => $v1) {
                $row[$i] = iconv('utf-8', 'GBK', $v1);
            }

            fputcsv($fp, $row);
           // var_dump($v);
        }
        $insert_data = [
            'name' => "充值记录".$fileName,
            'url' => "/data/xls/".$fileName,
            'ctime' => time()
        ];
        $re = db('hands_fiels')->insertGetId($insert_data);
        if($re){
            return [
                'code' => 1,
                'msg'  => '文件已生成'
            ];
        }else{
            return [
                'code' => 0,
                'msg'  => '文件生成失败'
            ];
        }
    }

    //所有商户列表
    public function promote(){
        return view("promote");
    }

    //获取所有商户数据
    public function getpromote(){
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
            $account = input('get.account');
            $nickname = input('get.nickname');
            $where = "identity=1";

            if($account){
                $where .= " and account='".$account."'";
            }
            if($nickname){
                $where .= " and nickname='".$nickname."'";
            }
            //---------------END--------------//
            $data  = db('promote')
                ->where($where)
                ->order('id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('promote')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']           = $v['id'];
                    $v['account']      = $v['account'];
                    $v['nickname']     = $v['nickname'];
                    $v['mobile_phone'] = $v['mobile_phone'];
                    $v['email']        = $v['email'];
                    $v['referee_id']   = $v['referee_id'] ? getRef($v['referee_id']) : '';
                    $v['real_name']    = $v['real_name'];
                    $v['h5_rec']       = number_format(db('promote_deposit')->where("promote_id=".$v['id']." and pay_way in (4,5,6,7) and pay_type != 2 and pay_status=1")->value('sum(pay_amount)'),2);
                    $v['t0_rec']       = number_format(db('promote_deposit')->where("promote_id=".$v['id']." and pay_type = 2 and pay_status=1")->value('sum(pay_amount)'),2);
                    $v['pc_rec']       = number_format(db('promote_deposit')->where("promote_id=".$v['id']." and pay_way in (1,2,3) and pay_type != 2 and pay_status=1")->value('sum(pay_amount)'),2);
                    $v['status']       = $v['status'];
                    $v['comm_type']       = $v['comm_type'];
                    $v['level_title']  = getLevel($v['level_id']);
                    $v['create_time']  = $v['create_time'] ? date('Y-m-d H:i:s', $v['create_time']) : '';
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

    //编辑商户资料
    public function edit(){
        if(!request()->isPost()){
            $id = input('get.id');
            //查询所有档位
            $level = db('promote_level')->order('ident asc')->select();

            //查询用户资料
            $pro_data = db('promote')->where("id=".$id)->find();
            $this->assign('pro_data',$pro_data);
            $this->assign('level',$level);
            return $this->fetch();
        }else{
            $data = input('post.');
            if($data){
                $up_data = [
                    'nickname' => $data['nickname'],
                    'mobile_phone' => $data['mobile_phone'],
                    'email'    => $data['email'],
                    'comm_type' => $data['comm_type'],
                    'level_id' => $data['level_id']
                ];

                $result = db('promote')->where("id=".$data['id'])->update($up_data);
                if($result){
                    return [
                        'code' => 1,
                        'msg'  => '编辑成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '编辑失败'
                    ];
                }
            }
        }
    }

    //商户信息详情
    public function details(){
        $id = input('get.id');
        $data = [];
        $account = db('promote')->where("id=".$id)->find();
        //充值
        //总充值额
        $where = "promote_id=".$id." and pay_status=1";
        $data['all_money'] = number_format(db('promote_deposit')->where($where)->value('sum(pay_amount)'),2);
        $data['recharge'] = [];

        $data['recharge'] = [
            'pc' => number_format(db('promote_deposit')->where($where . " and pay_way in (1,2,3)")->value('sum(pay_amount)'),2),
            'h5' => number_format(db('promote_deposit')->where($where . " and pay_way in (4,5,6,7) and pay_type!=2")->value('sum(pay_amount)'),2),
            't0' => number_format(db('promote_deposit')->where($where . " and pay_type=2")->value('sum(pay_amount)'),2),
        ];

        //总结算额
        $data['all_rech_money'] = number_format(db('promote_deposit')->where($where . " and status = 1")->value('sum(pay_amount)'),2);
        $data['not_rech_money'] = number_format(db('promote_deposit')->where($where . " and status = 0")->value('sum(pay_amount)'),2);

        $sett_already_where = "promote_id=".$id." and status=1 and pay_status = 1";//已结算
        $sett_not_where = "promote_id=".$id." and status=0 and pay_status = 1";//未结算
        $data['sett'] = [
            'already' => [
                'pc' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_way in (1,2,3) and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_wetch' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_way = 4 and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_alipay' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_way = 5 and pay_type!=2")->value('sum(pay_amount)'),2),
                //'t0' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_type=2")->value('sum(pay_amount)'),2),
            ],
            'not' => [
                'pc' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_way in (1,2,3) and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_wetch' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_way = 4 and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_alipay' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_way = 5 and pay_type!=2")->value('sum(pay_amount)'),2),
                //'t0' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_type=2")->value('sum(pay_amount)'),2),
            ]
        ];

        //提现
        $with_already_where = "promote_id=".$id."  and status=4";//已提现
        $data['all_with_money'] = number_format(db('auth_withdraw')->where($with_already_where)->value('sum(money)'),2);
        $data['not_with_money'] = db('promote')->field("sum(money+h5_wetch_money+h5_alipay_money) not_money")->where("id=".$id)->find();
        $data['with'] = [];

        $data['with'] = [
            'already' => [
                'pc' => number_format(db('auth_withdraw')->where($with_already_where." and auth_way=0")->value('sum(money)'),2),
                'h5_wetch' => number_format(db('auth_withdraw')->where($with_already_where." and auth_way=1")->value('sum(money)'),2),
                'h5_alipay' => number_format(db('auth_withdraw')->where($with_already_where." and auth_way=2")->value('sum(money)'),2),
            ],

            //未提现
            'not'    => [
                'pc' => db('promote')->where("id=".$id)->value('money') + db('promote')->where("id=".$id)->value('t0_money'),
                'h5_wetch' => db('promote')->where("id=".$id)->value('h5_wetch_money'),
                'h5_alipay' => db('promote')->where("id=".$id)->value('h5_alipay_money'),
            ]
        ];


        $this->assign('data',$data);
        $this->assign('account',$account);
        return view("details");
    }
    //修改商户账号状态
    public function upstatus(){
        $id  = input('get.id');
        $status = db('promote')->where("id=".$id)->value("status");
        if($status){
            $up_status = 0;
        }else{
            $up_status = 1;
        }
        db('promote')->where("id=".$id)->update(['status' => $up_status]);
        return [
            'code' => 1,
            'status' => $up_status
        ];
    }

    //分配用户
    public function addpro(){
        if(!request()->isPost()){
            //生成商户号
            $account = generatePro();

            //查询所有档位
            $level = db('promote_level')->order('ident asc')->select();

            // 商户列表
            $map['identity'] = 1;
            $account_data = db('promote')->field('id,account')->where($map)->select();

            $this->assign('account_data',$account_data);
            $this->assign('account',$account);
            $this->assign('level',$level);
            return $this->fetch();
        }else{
            
            $data = input('post.');

            $re = checkPro($data['account'], $data['nickname']);
            if($re['re'] == 0){
                return [
                    'code' => 1,
                    'msg' => $re['msg']
                ];
                exit;
            }else{
                $ins_data = [
                    'account'       => $data['account'],
                    'nickname'      => $data['nickname'],
                    'mobile_phone'  => $data['mobile_phone'],
                    'email'         => $data['email'],
                    'level_id'      => $data['level_id'],
                    'comm_type'      => $data['comm_type'],
                    'referee_id'    => $data['referee_id'],
                    'hands_pass'    => $this->think_ucenter_md5($data['hands_pass'], self::DATA_AUTH_KEY),
                    'identity'      => 1,
                    'create_time'   => time()
                ];
                $result = db('promote')->insertGetId($ins_data);
                if($result){
                    return [
                        'code' => 1,
                        'msg' => '分配成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg' => '分配失败'
                    ];
                }
            }
        }

    }

    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    //PC提现列表
    public function with(){
        //已提现总金额
        $all_with = db('auth_withdraw')->where("status=4")->value("sum(money)");
        $this->assign('all_with',$all_with ? $all_with : '0.00');
        //当日提现金额
        $now_time = strtotime(date("Y-m-d",time()) . " 00:00:00");
        $with = db('auth_withdraw')->where("status=4" . " and ctime > ".$now_time)->value("sum(money)");

        //佣金总额
        $commission_data = db('auth_withdraw')->where("status=4")->select();
        $commission = 0;
        foreach ($commission_data as $key=>$val){
            $commission += $val['money'] * $val['rate'];
        }
        //提现中金额
        $is_with = db('auth_withdraw')->where("status=0 or status=2 or status=3")->value("sum(money)");

        $this->assign('with',$with ? $with : '0.00');
        $this->assign('commission',$commission ? $commission : '0.00');
        $this->assign('is_with',$is_with ? $is_with : '0.00');
        return view("with");
    }

    //PC获取提现页数据
    public function getwith(){
        if (!request()->isGet()) {
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        } else {

            $page  = input('get.page') ? input('get.page') : 1;
            $limit = input('get.limit') ? input('get.limit') : 10;
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询
            $sta       = strtotime(input('get.start_time'));
            $end       = strtotime(input('get.first_time'));
            $account = input('get.account');
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

            if($status != ''){
                $where .= "a.status=".$status;
            }

            //---------------END--------------//
            $data  = db('auth_withdraw')
                ->alias('a')
                ->field('a.*,p.account,p.nickname,p_l.revenue')
                ->join('promote p','p.id=a.promote_id')
                ->join('promote_level p_l', 'p.level_id = p_l.id', 'left')
                ->where($where)
                ->order('a.id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('auth_withdraw')
                ->alias('a')
                ->field('a.*,p.account,p.nickname')
                ->join('promote p','p.id=a.promote_id')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']           = $v['id'];
                    $v['account']      = $v['account'];
                    $v['nickname']     = $v['nickname'];
                    $v['money'] = $v['money'];
                    $v['rate'] = $v['rate'];
                    $v['actual_money'] = $v['actual_money'];
                    $v['auth_way'] = $this->getWithFrom($v['auth_way']);
                    $v['type'] =  db('promote')->where('id',$v['promote_id'])->value('comm_type');
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

    //H5提现列表
    public function withwap(){
        //已提现总金额
        $all_with = db('auth_withdraw')->where("status=4 and type=2")->value("sum(money)");
        $this->assign('all_with',$all_with ? $all_with : '0.00');
        //当日提现金额
        $now_time = strtotime(date("Y-m-d",time()) . " 00:00:00");
        $with = db('auth_withdraw')->where("status=4 and type=2" . " and ctime > ".$now_time)->value("sum(money)");

        //佣金总额
        $commission_data = db('auth_withdraw')->where("status=4 and type=2")->select();
        $commission = 0;
        foreach ($commission_data as $key=>$val){
            $commission += $val['money'] * $val['rate'];
        }
        //提现中金额
        $is_with = db('auth_withdraw')->where("(status=0 or status=2 or status=3)  and type=2")->value("sum(money)");

        $this->assign('with',$with ? $with : '0.00');
        $this->assign('commission',$commission ? $commission : '0.00');
        $this->assign('is_with',$is_with ? $is_with : '0.00');
        return view("withwap");
    }

    //H5获取提现页数据
    public function getwithwap(){
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
            $nickname = input('get.nickname');
            $status = input('get.status');
            $where = 'a.type = 2';

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
                $where .= " and a.account='".$account."'";
            }
            if($nickname){
                $where .= " and a.nickname='".$nickname."'";
            }
            if($status != ''){
                $where .= "a.status=".$status;
            }
            //---------------END--------------//
            $data  = db('auth_withdraw')
                ->alias('a')
                ->field('a.*,p.account,p.nickname,p_l.h5_revenue')
                ->join('promote p','p.id=a.promote_id')
                ->join('promote_level p_l', 'p.level_id = p_l.id', 'left')
                ->where($where)
                ->order('a.id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('auth_withdraw')
                ->alias('a')
                ->field('a.*,p.account,p.nickname')
                ->join('promote p','p.id=a.promote_id')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']           = $v['id'];
                    $v['account']      = $v['account'];
                    $v['nickname']     = $v['nickname'];
                    $v['money'] = $v['money'];
                    $v['revenue'] = $v['h5_revenue'];
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
     * getStatus [获取结算状态]
     *
     * author dear
     * @param $status
     * @return string
     */
    private function getstatus($status)
    {
        switch ($status){
            case 1:
                $res = '已结算';
                break;
            case 0:
                $res = '未结算';
                break;

        }
        return $res;
    }

    /**
     * getStatus [获取提现来源]
     *
     * author dear
     * @param $status
     * @return string
     */
    private function getwithfrom($status)
    {
        switch ($status){
            case 0:
                $res = 'PC扫码';
                break;
            case 1:
                $res = 'H5微信';
                break;
            case 2:
                $res = 'H5支付宝';
                break;


        }
        return $res;
    }

    public function bussinfo(){
        return view("bussinfo");
    }
    public function buss_get_info(){
        $arrData = db("buss")
            ->alias("b")
            ->field("b.business_id,u.token,u.account,u.balance,u.phone,b.real_name,b.idcard,b.company_name,b.business_license")
            ->join('user u','u.business_id = b.business_id')
            ->select();
        $count = db('buss')->count();
        return [
            'code'  => 0,
            'msg'   => '',
            'count' => $count,
            'data'  => $arrData
        ];
    }

    public function addbussinfo(){
        if(request()->isPost()){
            $data['business_id'] = input("post.business_id");
            $data['real_name'] = input("post.real_name");
            $data['idcard'] = input("post.idcard");
            $data['company_name'] = input("post.company_name");
            $file = input("file.business_license");
            $filename = ROOT_PATH."/uploads/buss/";
            $icon = $file->move($filename);
            if($icon){
                $data['business_license'] = $icon->getSaveName();
                $bool = db("buss")->insert($data);
                $this->statusUrl($bool,"/bussinfo","添加buss成功");
            }else{
                $err['erricon'] = $file->getError();
                $this->error($err);
                exit;
            }
        }else{
            return view("addbussinfo");
        }
    }
}