<?php
namespace app\api\controller;

use think\Controller;

class Order extends Controller{

    public function pay(){
        $data = $_REQUEST;
        $account = $data['account'];
        $pay_way = $data['pay_way'];
        $body = $data['body'];
        $notify_url = $data['notify_url'];
        $return_url = $data['return_url'];
        $resqn = $data['resqn'];
        $pay_amount = $data['pay_amount'];
        $key_token = db("promote")->field("paykey")->where(['account'=>$data['account']])->find();
        $token = $key_token['paykey'];
        $zs_sign = md5("account={$account}&pay_way={$pay_way}&body={$body}&notify_url={$notify_url}&resqn={$resqn}&pay_amount={$pay_amount}{$token}");
        $url = "http://www.zhishengwh.com/api/Zspay/index.html?account={$account}&pay_way={$pay_way}&body={$body}&notify_url={$notify_url}&return_url={$return_url}&resqn={$resqn}&pay_amount={$pay_amount}&zs_sign={$zs_sign}";
        header("location:".$url);
    }

    public function pay2(){
        $data = $_REQUEST;
        $account = $data['account'];
        $pay_way = $data['pay_way'];
        $body = $data['body'];
        $notify_url = $data['notify_url'];
        $return_url = $data['return_url'];
        $resqn = $data['resqn'];
        $pay_amount = $data['pay_amount'];
        $key_token = db("promote")->field("paykey")->where(['account'=>$data['account']])->find();
        $token = $key_token['paykey'];
        $channe_type = $data['channe_type'];;
        $zs_sign = md5("ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}{$token}");
        $url = $_SERVER['HTTP_ORIGIN']."/api/Zspay/allindex.html";
        $urlData ="ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RETURN_URL={$return_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}&ZS_SIGN={$zs_sign}&CHANNE_TYPE={$channe_type}";
        echo $this->request_ord($url,$urlData);
    }

    public function pay3(){
        $data = $_REQUEST;
        $account = $data['account'];
        $pay_way = $data['pay_way'];
        $body = $data['body'];
        $notify_url = $data['notify_url'];
        $return_url = $data['return_url'];
        $resqn = $data['resqn'];
        $pay_amount = $data['pay_amount'];
        $key_token = db("promote")->field("paykey")->where(['account'=>$data['account']])->find();
        $token = $key_token['paykey'];
        $channe_type = $data['channe_type'];;
        $zs_sign = md5("ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}{$token}");
        $url = $_SERVER['HTTP_ORIGIN']."/api/Zspay/juhuiindex.html";
        $urlData ="ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RETURN_URL={$return_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}&ZS_SIGN={$zs_sign}&CHANNE_TYPE={$channe_type}";
        echo $this->request_ord($url,$urlData);
    }

    public function request_ord($url,$params){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $headers = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function testpay(){
        $resqnfun = 'zspay'.date('Ymd').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);

        $account = "zswh201807241709aug5tw1j";
        $pay_way = "支付宝";
        $body = "test";
        $notify_url = "http://www.zhishengwh.com/asyn_url.php";
        $return_url = "http://www.zhishengwh.com/success_url.php";
        $resqn = $resqnfun;
        $pay_amount = "1";
        $key_token = db("promote")->field("paykey")->where(['account'=>$account])->find();
        $token = $key_token['paykey'];
        $channe_type = "固额通道(H5)";
        $zs_sign = md5("ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}{$token}");
        $url = "http://www.zhishengwh.com/api/Zspay/juhuiindex.html";
        $urlData ="ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RETURN_URL={$return_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}&ZS_SIGN={$zs_sign}&CHANNE_TYPE={$channe_type}";
        echo $this->request_ord($url,$urlData);

    }

}