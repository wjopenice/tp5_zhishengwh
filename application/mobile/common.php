<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//获取当前商户ID
function get_pro_id(){
    $promote = session('account');
    return $promote['id'];
}
/**
 * 获取服务器端IP地址
 * @return string
 */
function get_server_ip() {

    if (isset($_SERVER)) {

        if ($_SERVER['SERVER_ADDR']) {

            $server_ip = $_SERVER['SERVER_ADDR'];
        } else {

            $server_ip = $_SERVER['LOCAL_ADDR'];
        }
    } else {

        $server_ip = getenv('SERVER_ADDR');
    }

    return $server_ip;
}

//判断充值状态
function getPayStatus($status) {
    switch ($status) {
        case '0':
            return '待支付';
            break;
        case '1':
            return '支付成功';
            break;
        case '2':
            return '超时未支付';
            break;
        case '3':
            return '余额不足';
            break;

    }
}

//判断提现状态
function getWithStatus($status) {
    switch ($status) {
        case '0':
            return '待审核';
            break;
        case '1':
            return '审核不通过';
            break;
        case '2':
            return '审核通过';
            break;
        case '3':
            return '打款中';
            break;
        case '4':
            return '提现完成';
            break;
    }
}

//判断充值类型
function getPayType($source) {
    $type = '';
    switch ($source) {
        case '1':
            $type = '支付宝';
            break;
        case '2':
            $type = '微信';
            break;
        case '3':
            $type = '网银';
            break;
        case '4':
            $type = 'H5微信';
            break;
        case '5':
            $type = 'H5支付宝';
            break;
        case '6':
            $type = 'H5银联';
            break;
        case '7':
            $type = '手机QQ';
            break;
    }
    return $type;
}

//判断充值第三方名称
function getPayClass($type) {
    $t = '';
    switch ($type) {
        case '1':
            $t = '通联';
            break;
        case '2':
            $t = '环迅';
            break;
        case '3':
            $t = '千玺';
            break;
        case '4':
            $t = '爱加密';
            break;
        case '5':
            $t = '点缀';
            break;
        case '6':
            $t = '兴业';
            break;
        case '7':
            $t = '聚森';
            break;
        case '8':
            $t = '快接';
            break;
    }
    return $t;
}

//通过银行标识获取银行名称
function getBankName($key){
    $bank = Config('bank');
    $list = $bank['bank_list'];
    if(array_key_exists($key,$list)){
        return $list[$key];
    }else{
        return '';
    }

}

//通过bank_id获取银行名称
function getIdBankName($bank_id){
    $bank_name = db('binding_bank')->where("bank_id",$bank_id)->value('bank_name');
    $bank = Config('bank');
    $list = $bank['bank_list'];
    if(array_key_exists($bank_name,$list)){
        return $list[$bank_name];
    }else{
        return '';
    }
}

//获取支付来源主体
function getSubject($id){
    $subject = db('pay_interface')->where("id",$id)->value('pay_title');
    if($subject){
        return $subject;
    }else{
        return '';
    }
}

//通过bank_id获取银行卡号
function getIdBankNum($bank_id){
    $bank_number = db('binding_bank')->where("bank_id",$bank_id)->value('bank_number');

    return $bank_number;

}

//生成商户APPKEY
function generateKey($param){
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $key = "";
    for($i=0;$i<$param;$i++)
    {
        $key .= $str{mt_rand(0,32)};    //生成php随机数
    }
    return $key;
}

//生成商户号
function generatePro(){
    $a = range(0,9);
    for($i=0;$i<8;$i++){
        $b[] = array_rand($a);
    }
    $pro_num = join("",$b);
    $find = db('promote')->where("account='".$pro_num."'")->find();
    if($find){
        $this->generatePro();
    }
    return $pro_num;
}


//获取银行所属地区
function getBankRegion($str){
    $str_arr = explode(',',$str);
    $new_str = '';
    foreach($str_arr as $value){
        $new_str .= db('areas')->where("area_id=".$value)->value('area_name') . ",";
    }
    return substr($new_str,0,-1);
}

//获取推荐人
function getRef($id){
    if($id){
        $re_account = db('promote')->where("id=".$id)->value('account');
        return $re_account;
    }
}

//查询平台档位
function getLevel($id){
    if($id){
        $level_title = db('promote_level')->where("id=".$id)->value('level');
        return $level_title;
    }
}

//检测商户是否存
function checkPro($account,$nickname){
    if($account){
        $acc_find = db('promote')->where("account='".$account."'")->find();
    }
    if($nickname){
        $nick_find = db('promote')->where("nickname='".$nickname."'")->find();
    }
    if($acc_find){
        return [
            're'  => 0,
            'msg'=>'商户号已存在',
        ];
    }
    if($nick_find){
        return [
            're'  => 0,
            'msg'=>'商户名称已存在',
        ];
    }
    if(!$acc_find && !$nick_find){
        return [
            're'  => 1,
        ];
    }
}


function think_ucenter_md5($str, $key = 'ThinkUCenter'){
    return '' === $str ? '' : md5(sha1($str) . $key);
}

//添加商户操作记录
function rwLog($data){
    $re = db('promote_logs')->insert($data);
    return $re;
}

//查询所有权限
function getALLRole(){
    $class = db('hands_menu')->where("praendid=0")->select();
    foreach($class as $key=>$val){
        $class[$key]['second'] = db('hands_menu')->where("praendid",$val['id'])->select();
    }
    return $class;
}

//判断通道充值数据情况
function judgeRec($data){
    if(count($data) < 1){
        if(!isset($data[0])){
            $data[0] = [
                'pay_type' => 1,
                'pay_amount' => '0.00',
                'type_name' => '通联'
            ];
        }
        if(!isset($data[1])){
            $data[1] = [
                'pay_type' => 2,
                'pay_amount' => '0.00',
                'type_name' => '环迅'
            ];
        }
        if(!isset($data[2])){
            $data[2] = [
                'pay_type' => 3,
                'pay_amount' => '0.00',
                'type_name' => '钱台'
            ];
        }
        if(!isset($data[3])){
            $data[3] = [
                'pay_type' => 4,
                'pay_amount' => '0.00',
                'type_name' => '爱加密'
            ];
        }
        if(!isset($data[4])){
            $data[4] = [
                'pay_type' => 5,
                'pay_amount' => '0.00',
                'type_name' => '点缀'
            ];
        }
        if(!isset($data[5])){
            $data[5] = [
                'pay_type' => 6,
                'pay_amount' => '0.00',
                'type_name' => '兴业'
            ];
        }
        if(!isset($data[6])){
            $data[6] = [
                'pay_type' => 7,
                'pay_amount' => '0.00',
                'type_name' => '融宝'
            ];
        }
        if(!isset($data[7])){
            $data[7] = [
                'pay_type' => 8,
                'pay_amount' => '0.00',
                'type_name' => '快接'
            ];
        }
    }else{
        $def_array = array(
            0 =>array(
                'pay_type' =>  1,
                'pay_amount' =>  '0.00',
            ),
            1 =>array(
                'pay_type' =>  2,
                'pay_amount' =>  '0.00',
            ),
            2 =>array(
                'pay_type' =>  3,
                'pay_amount' =>  '0.00',
            ),
            3 =>array(
                'pay_type' =>  4,
                'pay_amount' =>  '0.00',
            ),
            4 =>array(
                'pay_type' =>  5,
                'pay_amount' =>  '0.00',
            ),
            5 =>array(
                'pay_type' =>  6,
                'pay_amount' =>  '0.00',
            ),
            6 =>array(
                'pay_type' =>  7,
                'pay_amount' =>  '0.00',
            ),
            7 =>array(
                'pay_type' =>  8,
                'pay_amount' =>  '0.00',
            ),
        );
        foreach ($def_array as $key=>$val) {

            foreach ($data as $k => $v) {

                if($v['pay_type'] == $val['pay_type']){
                    $def_array[$key] = $v;
                }
            }

            $def_array[$key]['type_name'] = getPayClass($val['pay_type']);

        }
        $data = $def_array;
    }

    return $data;
}

//获取七天的起始时间戳
function getSevenTime(){

    //获取第一天的时间
    $one_day = date("Y-m-d",strtotime("-6 day"));
    //获取第二天的时间
    $two_day = date("Y-m-d",strtotime("-5 day"));
    //获取第三天的时间
    $three_day = date("Y-m-d",strtotime("-4 day"));
    //获取第四天的时间
    $four_day = date("Y-m-d",strtotime("-3 day"));
    //获取第五天的时间
    $five_day = date("Y-m-d",strtotime("-2 day"));
    //获取第六天的时间
    $six_day = date("Y-m-d",strtotime("-1 day"));

    //获取当前时间
    $today = date('Y-m-d',time());


    $seven_time = [
        '0' => [
            'time' => $one_day,
        ],
        '1' => [
            'time' => $two_day,
        ],
        '2' => [
            'time' => $three_day,
        ],
        '3' => [
            'time' => $four_day,
        ],
        '4' => [
            'time' => $five_day,
        ],
        '5' => [
            'time' => $six_day,
        ],
        '6' => [
            'time' => $today,
        ],
    ];
    return $seven_time;
}


//获取平台各种数据
function getUserData()
{
    $data = [];
    $where = 1;
    // 获取今日开始时间戳和结束时间戳
    $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
    //平台总充值额
    //总充值金额
    $data['all_data'] = db('promote_deposit')->where($where . ' and pay_status=1')->value('sum(pay_amount)');
    //平台总提现额
    $data['all_with'] = db('auth_withdraw')->where($where . " and status=4")->value("sum(money)");
    //平台总结算额
    $data['all_sett'] = db('promote_deposit')->where($where . " and pay_status=1 and status=1")->value("sum(pay_amount) pay_amount");
    //平台总商户数
    $data['all_pro_counts'] = db('promote')->count();

    //商户基本信息
    //总订单数
    $data['all_orders'] = db('promote_deposit')->count();
    //成功订单数
    $data['all_success_orders'] = db('promote_deposit')->where('pay_status',1)->count();
    //失败订单数
    $data['all_fail_orders'] = db('promote_deposit')->where('pay_status',0)->count();
    //总订单总额
    $data['all_orders_money'] = db('promote_deposit')->where($where )->value('sum(pay_amount)');
    //成功额
    $data['all_success_money'] = db('promote_deposit')->where('pay_status',1)->value('sum(pay_amount)');
    //失败额
    $data['all_fail_money'] = db('promote_deposit')->where('pay_status',0)->value('sum(pay_amount)');

    //今日充值订单总数
    $data['today_orders'] = db('promote_deposit')->where($where . ' and (create_time >= ' . $beginToday . ' and create_time <= ' . $endToday . ')')->count();
    //今日失败订单数
    $data['today_fail_orders'] = db('promote_deposit')->where($where . ' and pay_status = 0 and (create_time >= ' . $beginToday . ' and create_time <= ' . $endToday . ')')->count();
    //今日成功订单数
    $data['today_success_orders'] = db('promote_deposit')->where($where . ' and pay_status = 1 and (create_time >= ' . $beginToday . ' and create_time <= ' . $endToday . ')')->count();


    //今日充值订单总额
    $data['today_orders_money'] = db('promote_deposit')->where($where . ' and (create_time >= ' . $beginToday . ' and create_time <= ' . $endToday . ')')->value('sum(pay_amount)');
    //成功额
    $data['today_success_money'] = db('promote_deposit')->where($where . ' and pay_status = 1 and (create_time >= ' . $beginToday . ' and create_time <= ' . $endToday . ')')->value('sum(pay_amount)');
    //失败额
    $data['today_fail_money'] = db('promote_deposit')->where($where . ' and pay_status = 0 and (create_time >= ' . $beginToday . ' and create_time <= ' . $endToday . ')')->value('sum(pay_amount)');

    //当前提现记录数
    $data['with_strip'] = db('auth_withdraw')->where($where . " and status != 1")->count();

    //当前已提现总额
    $data['with_money'] = db('auth_withdraw')->where($where . " and status = 4")->value("sum(money)");

    //当前可提现总额
    $with_not = db('promote')->field("(money + h5_wetch_money + h5_alipay_money + t0_money) as with_not")->find();
    $data['with_not'] = $with_not['with_not'];

    //今日提现记录数
    $data['today_with_strip'] = db('auth_withdraw')->where($where . ' and status != 1 and (ctime >= ' . $beginToday . ' and ctime <= ' . $endToday . ')')->count();

    //今日提现记录总额
    $data['today_with_money'] = db('auth_withdraw')->where($where . ' and status != 1 and (ctime >= ' . $beginToday . ' and ctime <= ' . $endToday . ')')->value("sum(money)");

    //未结算总额
    $data['not_sett'] = db('promote_deposit')->where($where . " and pay_status=1 and status=0")->value("sum(pay_amount) pay_amount");
    return $data;
}
