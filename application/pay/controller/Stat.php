<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/19
 * Time: 11:39
 */
namespace app\pay\controller;
use app\common\controller\Pay;
class Stat extends Pay {

    //充值统计
    public function recharge(){

        // 获取今日开始时间戳和结束时间戳
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        //总充值金额
        $all_data = db('promote_deposit')->where('pay_status',1)->value('sum(pay_amount)');
        //各通道总充值金额
        $class = db('promote_deposit')->field('pay_type,sum(pay_amount) pay_amount')->where('pay_status',1)->group('pay_type')->select();
        foreach($class as $k=>$v){
            $class[$k]['type_name'] = getPayClass($v['pay_type']);
        }

        //总充值金额
        $today_data = db('promote_deposit')->where('pay_status=1 and (create_time >= '.$beginToday.' and create_time <= '.$endToday.')')->value('sum(pay_amount)');
        //各通道当日充值金额
        $today_class = db('promote_deposit')->field('pay_type,sum(pay_amount) pay_amount')->where('pay_status=1 and (create_time >= '.$beginToday.' and create_time <= '.$endToday.')')->group('pay_type')->select();
        $today_class = judgeRec($today_class);


        $this->assign('all_data',$all_data);
        $this->assign('class',$class);

        $this->assign('today_data',$today_data ? : '0.00');
        $this->assign('today_class',$today_class);
        return view("recharge");
    }

    //获取各通道总充值金额json
    public function getjsonrec(){
        //各通道总充值金额
        $class = db('promote_deposit')->field('pay_type,sum(pay_amount) pay_amount')->where('pay_status',1)->group('pay_type')->select();
        foreach($class as $k=>$v){
            $class[$k]['type_name'] = getPayClass($v['pay_type']);
        }
        echo json_encode($class);
    }

    //获取各通道总充值金额json
    public function getjsonrectoday(){
        // 获取今日开始时间戳和结束时间戳
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        //各通道当日充值金额
        $today_class = db('promote_deposit')->field('pay_type,sum(pay_amount) pay_amount')->where('pay_status=1 and (create_time >= '.$beginToday.' and create_time <= '.$endToday.')')->group('pay_type')->select();
        $today_class = judgeRec($today_class);
        echo json_encode($today_class);
    }

    //获取一周内各通道充值额
    public function weekalljson()
    {
        $time = getSevenTime();
        $data_new = [
            '0' => [
                'class' => 1,
                'name' => '通联'
            ],
            '1' => [
                'class' => 2,
                'name' => '环讯'
            ],
            '2' => [
                'class' => 3,
                'name' => '千玺'
            ],
            '3' => [
                'class' => 4,
                'name' => '爱加密'
            ],
            '4' => [
                'class' => 5,
                'name' => '点缀'
            ],
            '5' => [
                'class' => 6,
                'name' => '兴业'
            ],
            '6' => [
                'class' => 7,
                'name' => '融宝'
            ],
            '7' => [
                'class' => 8,
                'name' => '快接'
            ]

        ];
        foreach ($data_new as $k => $v) {
            foreach ($time as $key => $val) {

                $data = db('promote_deposit')
                    ->field('pay_type,sum(pay_amount) pay_amount')
                    ->where('pay_status=1 and pay_type = '.$v['class'].' and (create_time >= ' . strtotime($val['time'] . "00:00:00") . ' and create_time <= ' . strtotime($val['time'] . "23:59:59") . ')')
                    ->find();
                if($data['pay_type'] == ''){
                    $data['pay_type'] = $v['class'];
                }
                if($data['pay_amount'] == ''){
                    $data['pay_amount'] = 0;
                }
                $time[$key]['data'] = $data;

            }
            $data_new[$k]['content'] = $time;
        }
            //array_multisort(array_column($time,'time'));
        echo json_encode($data_new);
    }

    //提现统计
    public function with(){

        // 获取今日开始时间戳和结束时间戳
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        //不同状态总提现金额
        $all_data = [
            'wait_data' => db('auth_withdraw')->where('status',0)->value('sum(money)'),//待审核
            'no_pass_data' => db('auth_withdraw')->where('status',1)->value('sum(money)'),//审核不通过
            'yes_pass_data' => db('auth_withdraw')->where('status',2)->value('sum(money)'),//审核通过
            'making_data' => db('auth_withdraw')->where('status',3)->value('sum(money)'),//打款中
            'complete_data' => db('auth_withdraw')->where('status',4)->value('sum(money)'),//提现完成
        ];
        //PC和H5各提现金额
        $class = [
            'wait_data' => db('auth_withdraw')->field('sum(money) money')->where('status',0)->group('auth_way')->select(),//待审核
            'no_pass_data' => db('auth_withdraw')->field('sum(money) money')->where('status',1)->group('auth_way')->select(),//审核不通过
            'yes_pass_data' => db('auth_withdraw')->field('sum(money) money')->where('status',2)->group('auth_way')->select(),//审核通过
            'making_data' => db('auth_withdraw')->field('sum(money) money')->where('status',3)->group('auth_way')->select(),//打款中
            'complete_data' => db('auth_withdraw')->field('sum(money) money')->where('status',4)->group('auth_way')->select(),//提现完成
        ];

        //当日不同状态提现金额
        $today_time_map = ' and (audit_time >= '.$beginToday.' and audit_time <= '.$endToday.")";
        $today_data = [
            'wait_data' => db('auth_withdraw')->where('status=0 ' . $today_time_map)->value('sum(money)'),//待审核
            'no_pass_data' => db('auth_withdraw')->where('status=1 ' . $today_time_map)->value('sum(money)'),//审核不通过
            'yes_pass_data' => db('auth_withdraw')->where('status=2 ' . $today_time_map)->value('sum(money)'),//审核通过
            'making_data' => db('auth_withdraw')->where('status=3 ' . $today_time_map)->value('sum(money)'),//打款中
            'complete_data' => db('auth_withdraw')->where('status=4 ' . $today_time_map)->value('sum(money)'),//提现完成
        ];
        //当日PC和H5各提现金额
        $today_class = [
            'wait_data' => db('auth_withdraw')->field('sum(money) money')->where('status=0 ' . $today_time_map)->group('auth_way')->select(),//待审核
            'no_pass_data' => db('auth_withdraw')->field('sum(money) money')->where('status=1 ' . $today_time_map)->group('auth_way')->select(),//审核不通过
            'yes_pass_data' => db('auth_withdraw')->field('sum(money) money')->where('status=2 ' . $today_time_map)->group('auth_way')->select(),//审核通过
            'making_data' => db('auth_withdraw')->field('sum(money) money')->where('status=3 ' . $today_time_map)->group('auth_way')->select(),//打款中
            'complete_data' => db('auth_withdraw')->field('sum(money) money')->where('status=4 ' . $today_time_map)->group('auth_way')->select(),//提现完成
        ];

        if($class){
            foreach ($class as $key => $value) {
                if(!($value && isset($value['0']))){
                    $class[$key]['0']['money'] = 0;
                } 
                if(!($value && isset($value['1']))){
                    $class[$key]['1']['money'] = 0;
                } 
                if(!($value && isset($value['2']))){
                    $class[$key]['2']['money'] = 0;
                }

            }
        }
        if($today_class){
            foreach ($today_class as $key => $value) {
                if(!($value && isset($value['0']))){
                    $today_class[$key]['0']['money'] = 0;
                } 
                if(!($value && isset($value['1']))){
                    $today_class[$key]['1']['money'] = 0;
                } 
                if(!($value && isset($value['2']))){
                    $today_class[$key]['2']['money'] = 0;
                }

            }
        }


        $this->assign('all_data',$all_data);
        $this->assign('class',$class);

        $this->assign('today_data',$today_data);
        $this->assign('today_class',$today_class);
        return view("with");
    }


    //分润管理
    public function profits(){

        //总充值金额
        $recharge_all_data = db('promote_deposit')->where('pay_status',1)->value('sum(pay_amount)');
        //总提现金额
        $withdraw_all_data = db('auth_withdraw')->where('status',4)->value('sum(money)');//提现完成

        //支付平台个数
        $pay_count = 5;

        //各通道总充值金额
        $class = db('promote_deposit')->field('pay_type,sum(pay_amount) pay_amount')->where('pay_status',1)->group('pay_type')->select();
        foreach($class as $k=>$v){
            $class[$k]['type_name'] = getPayClass($v['pay_type']);
        }

        $this->assign('recharge_all_data',$recharge_all_data);
        $this->assign('withdraw_all_data',$withdraw_all_data);
        $this->assign('pay_count',$pay_count);
        $this->assign('class',$class);
        return view("profits");
    }


}