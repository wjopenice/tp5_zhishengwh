<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/12
 * Time: 17:51
 */
    namespace app\common\controller;
    use think\Controller;
    class Api extends Controller {
        public function _initialize() {

        }

        protected function parameterall($data){
            unset($data['s']);
            if(count($data) == 0){
                return  ["status"=>"40000","msg"=>"参数为空"];
                exit;
            }
            //$account = $data['account'];
            if(empty($data['ACCOUNT'])){
                return  ["status"=>"40001","msg"=>"商户号为空"];
                exit;
            }
            $promote = db("promote")->where(['account'=>$data['ACCOUNT']])->find();
            if(empty($promote)){
                return  ["status"=>"40002","msg"=>"商户号不存在"];
                exit;
            }
            if($promote['status'] != 1){
                return  ["status"=>"40003","msg"=>"商户已经被禁用"];
                exit;
            }
            if(empty($data['NOTIFY_URL'])){
                return  ["status"=>"40004","msg"=>"异步回调地址为空"];
                exit;
            }
            if(empty($data['RETURN_URL'])){
                return  ["status"=>"40005","msg"=>"同步回调地址为空"];
                exit;
            }
            if(empty($data['RESQN'])){
                return  ["status"=>"40006","msg"=>"订单号为空"];
                exit;
            }
            if(empty($data['PAY_AMOUNT'])){
                return  ["status"=>"40007","msg"=>"缺少交易金额"];
                exit;
            }
            if(empty($data['PAY_WAY'])){
                return  ["status"=>"40008","msg"=>"缺少通道方式"];
                exit;
            }
            if(empty($data['BODY'])){
                return  ["status"=>"40009","msg"=>"缺少商品名称"];
                exit;
            }
            if(empty($data['ZS_SIGN'])){
                return  ["status"=>"40012","msg"=>"缺少验签"];
                exit;
            }
            $order = db("promote_deposit")->where(['pay_order_number'=>$data['RESQN']])->find();
            if($order){
                return  ["status"=>"40010","msg"=>"订单号重复"];
                exit;
            }
            //sign校验
            if($promote['paykey']){
                $account = $data['ACCOUNT'];
                $pay_way = $data['PAY_WAY'];
                $body = $data['BODY'];
                $notify_url = $data['NOTIFY_URL'];
                $resqn = $data['RESQN'];
                $pay_amount = $data['PAY_AMOUNT'];
                $token = $promote['paykey'];
                $sign = md5("ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}{$token}");
                if($sign != $data['ZS_SIGN']){
                    return array("status"=>'40013',"msg"=>"验签失败");
                    exit;
                }
            }else{
                return array("status"=>'40011',"msg"=>"密钥为空");
                exit;
            }
            return $promote;
        }

        protected function parameter($data){
            unset($data['s']);
            if(count($data) == 0){
                return  ["status"=>"40000","msg"=>"参数为空"];
                exit;
            }
            //$account = $data['account'];
            if(empty($data['account'])){
                return  ["status"=>"40001","msg"=>"商户号为空"];
                exit;
            }
            $promote = db("promote")->where(['account'=>$data['account']])->find();
            if(empty($promote)){
                return  ["status"=>"40002","msg"=>"商户号不存在"];
                exit;
            }
            if($promote['status'] != 1){
                return  ["status"=>"40003","msg"=>"商户已经被禁用"];
                exit;
            }
            if(empty($data['notify_url'])){
                return  ["status"=>"40004","msg"=>"异步回调地址为空"];
                exit;
            }
            if(empty($data['return_url'])){
                return  ["status"=>"40005","msg"=>"同步回调地址为空"];
                exit;
            }
            if(empty($data['resqn'])){
                return  ["status"=>"40006","msg"=>"订单号为空"];
                exit;
            }
            if(empty($data['pay_amount'])){
                return  ["status"=>"40007","msg"=>"缺少交易金额"];
                exit;
            }
            if(empty($data['pay_way'])){
                return  ["status"=>"40008","msg"=>"缺少通道方式"];
                exit;
            }
            if(empty($data['body'])){
                return  ["status"=>"40009","msg"=>"缺少商品名称"];
                exit;
            }
            if(empty($data['zs_sign'])){
                return  ["status"=>"40012","msg"=>"缺少验签"];
                exit;
            }
            $order = db("promote_deposit")->where(['pay_order_number'=>$data['resqn']])->find();
            if($order){
                return  ["status"=>"40010","msg"=>"订单号重复"];
                exit;
            }
            //sign校验
            if($promote['paykey']){
                $account = $data['account'];
                $pay_way = $data['pay_way'];
                $body = $data['body'];
                $notify_url = $data['notify_url'];
                $resqn = $data['resqn'];
                $pay_amount = $data['pay_amount'];
                $token = $promote['paykey'];
                $sign = md5("account={$account}&pay_way={$pay_way}&body={$body}&notify_url={$notify_url}&resqn={$resqn}&pay_amount={$pay_amount}{$token}");
                if($sign != $data['zs_sign']){
                    return array("status"=>'40013',"msg"=>"验签失败");
                    exit;
                }
            }else{
                return array("status"=>'40011',"msg"=>"密钥为空");
                exit;
            }
            return $promote;
        }
        //广州银行验签
        protected function gzcpsign($data){
            $signdata = md5('shid='.$data['shid'].'&bb='.$data['bb'].'&zftd='.$data['zftd'].'&ddh='.$data['ddh'].'&je='.$data['je'].'&ddmc='.$data['ddmc'].'&ddbz='.$data['ddbz'].'&ybtz='.$data['ybtz'].'&tbtz='.$data['tbtz'].'&'.$data['key']);
            return $signdata;
        }
        //派易云验签
        protected function pysign($data,$key){
            $str = "partner=".$data['partner']."&out_trade_no=".$data['out_trade_no']."&total_fee=".$data['total_fee']."&payment_type=".$data['payment_type']."&notify_url=".$data['notify_url']."&return_url=".$data['return_url']."&body=".$data['body'].$key;
            return md5($str);
        }
        //xx银行验签
        protected function signall($data,$key){
            $str = "{'transdate':'{$data['transdate']}','subject':{$data['subject']}','merchorder_no':'{$data['merchorder_no']}','paytype':'{$data['paytype']}','money':'{$data['money']}','merchantcode':'{$data['merchantcode']}','backurl':'{$data['backurl']}','service':'{$data['service']}','sign':''}{$key}";
            return $this->sha512key($str);
        }
        //其它银行验签
        protected function sign($data){
            //...........
        }
        //订单生成
        protected function build_order_no(){
            return date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        }
        //选择通道
        protected function select_channel($pay_way = 1){
            switch ($pay_way){
                case 1: $type_pay = "alipay_status";break;  //阿里扫码
                case 2: $type_pay = "wetch_status";break;  //微信扫码
                case 3: $type_pay = "h5_alipay_status";break; //h5阿里
                case 4: $type_pay = "h5_wetch_status";break;//h5微信
                default:$type_pay = "wetch_status";break;
            }
            $pay_data = db("pay_interface")->where(['status'=>1,$type_pay=>1])->select();
            if(!empty($pay_data)){
                $len = count($pay_data) - 1;
                $i = rand(0,$len);
                return $pay_data[$i];
            }else{
                return ["status"=>'40018',"msg"=>"暂未开通"];
                exit;
            }
        }
        //写入数据
        protected function add_deposit($promote_id,$data){
            $pay_way = $data['pay_way'];
            $GatewayType = 1;
            switch ($pay_way){
                case "支付宝": $GatewayType = "1";break;
                case "微信": $GatewayType = "2";break;
                case "网银": $GatewayType = "3";break;
                case "QQ支付": $GatewayType = "4";break;
                case "京东": $GatewayType = "5";break;
                case "快捷": $GatewayType = "6";break;
            }
            $re = db("promote_level")->field("revenue")->where(["id"=>1])->find();
            $actual_amount = $data['pay_amount'] - $data['pay_amount'] * $re['revenue'];

            $deposit_data = array(
                'order_number' => "ZSPAY".$this->build_order_no(),
                'pay_order_number' => $data['resqn'],
                'promote_id' => $promote_id,
                'promote_account'=>$data['account'],
                'pay_amount' => $data['pay_amount'],
                'pay_status' => 0,
                'pay_way' => $GatewayType, //支付方式
                'pay_source' => $data['pay_id'],
                'pay_ip' => $data['pay_ip'],
                'create_time' => time(),
                'notify_url' => $data['notify_url'],
                'return_url' =>$data['return_url'],
                'pay_type' => $data['pay_type'], //多通道
                'revenue'  => $re['revenue'],
                'actual_amount' => $actual_amount
            );
            return db("promote_deposit")->insert($deposit_data);
        }
        //写入数据2
        protected function add_deposit2($promote_id,$data){

            $pay_way = $data['PAY_WAY'];
            $GatewayType = 1;
            switch ($pay_way){
                case "支付宝": $GatewayType = "1";break;
                case "微信": $GatewayType = "2";break;
                case "网银": $GatewayType = "3";break;
                case "QQ支付": $GatewayType = "4";break;
                case "京东": $GatewayType = "5";break;
                case "快捷": $GatewayType = "6";break;
            }
            $re = db("promote_level")->field("revenue")->where(["id"=>1])->find();
            $actual_amount = $data['PAY_AMOUNT'] - $data['PAY_AMOUNT'] * $re['revenue'];

            $deposit_data = array(
                'order_number' => "ZSPAY".$this->build_order_no(), //给上游的订单号
                'pay_order_number' => $data['RESQN'], //客户订单号
                'promote_id' => $promote_id,
                'promote_account'=>$data['ACCOUNT'],
                'pay_amount' => $data['PAY_AMOUNT'],
                'pay_status' => 0,
                'pay_way' => $GatewayType, //支付方式
                'pay_source' => $data['pay_id'],
                'pay_ip' => $data['pay_ip'],
                'create_time' => time(),
                'notify_url' => $data['NOTIFY_URL'],
                'return_url' =>$data['RETURN_URL'],
                'pay_type' => $data['pay_type'], //多通道
                'revenue'  => $re['revenue'],
                'actual_amount' => $actual_amount
            );
            return db("promote_deposit")->insert($deposit_data);
        }

        public function get_order_number($order){
           $arrData = db("promote_deposit")->field("order_number")->where(["pay_order_number"=>$order])->find();
           return $arrData['order_number'];
        }
        //订单生成
        protected function build_order($resqn){
            $order_data = db("promote_deposit")->field("order_number")->where(["pay_order_number"=>$resqn])->find();
            return $order_data['order_number'];
        }
        //给支付金额加随机数
        protected function amountrand($pay_amount)
        {
            if(is_numeric($pay_amount)){
                //是数字
                if(preg_match("/^[0-9]+(.[0]{1,2})?$/",$pay_amount)){
                    $rand = round(mt_rand()/mt_getrandmax(),2);
                    $pay_amount = $pay_amount + $rand;
                }
            } else if(is_string($pay_amount)) {
                //是字符串
                if(substr($pay_amount, strpos($pay_amount,'.')+1) == "00"){
                    $rand = round(mt_rand()/mt_getrandmax(),2);
                    $pay_amount = $pay_amount + $rand;
                }
            }
            return $pay_amount;
        }

        protected function to_url_params(array $array)
        {
            $buff = "";
            foreach ($array as $k => $v)
            {
                if($v != "" && !is_array($v)){
                    $buff .= $k . "=" . $v . "&";
                }
            }

            $buff = trim($buff, "&");
            return $buff;
        }

        public function request_ord_post($url,$params){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $headers = array("Content-type: application/json;charset=utf-8","Accept: application/json","Cache-Control: no-cache", "Pragma: no-cache");
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        }

        public function request_pay($url,$params){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            $headers = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $response = curl_exec($ch);
            curl_close($ch);
            recordlog("pay5.txt","curl请求".json_encode($response,320));
            $arr = json_decode($response,true);
            return $arr;
        }

        public function request_post($url = '', $post_data = array()) {
            if (empty($url) || empty($post_data)) {return false;}
            $postUrl = $url;
            $curlPost = $post_data;
            $ch = curl_init();//初始化curl
            curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
            curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
            curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
            $data = curl_exec($ch);//运行curl
            curl_close($ch);
            return $data;
        }

        public function requestpost($url, $data){//file_get_content
            $opts = [
                'http' =>
                   [
                       'method'  => 'POST',
                       'header'  => 'Content-type: application/x-www-form-urlencoded',
                       'content' => $data
                   ]
            ];
            $context = stream_context_create($opts);
            $result = file_get_contents($url, false, $context);
            return $result;
        }

        //sha256加密
        protected function sha256key($password){
            return hash( "sha256", $password);
        }

        //sha512加密
        protected function sha512key($password){
            return hash( "sha512", $password);
        }

        protected function http_post_data($url, $data_string) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data_string))
            );
            ob_start();
            curl_exec($ch);
            $return_content = ob_get_contents();
            ob_end_clean();
            $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            return  $return_content;
        }

        public function qrcode($val="",$url=""){
            $path = ROOT_PATH . 'public' . DS . 'qrcode/';
            $saveurl = $path.$url;
            vendor('phpqrcode.phpqrcode');//引入插件类
            $errorCorrentionLevel = 'L'; //容错级别
            $matrixPoinSize = 4; //生成图片大小 //生成二维码,第二个参数为二维码保存路径
            \QRcode::png($val,$saveurl,$errorCorrentionLevel,$matrixPoinSize,2);
        }
    }