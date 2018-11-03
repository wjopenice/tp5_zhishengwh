<?php

function resqnfun(){
    return 'zspay'.date('Ymd').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

$account = "zswh201807241709aug5tw1j";
$pay_way = "支付宝";
$body = "test";
$notify_url = "http://www.zhishengwh.com/asyn_url.php";
$return_url = "http://www.zhishengwh.com/success_url.php";
$resqn = resqnfun();
$pay_amount = "1";
$key_token = db("promote")->field("paykey")->where(['account'=>$account])->find();
$token = $key_token['paykey'];
$channe_type = "固额通道(H5)";
$zs_sign = md5("ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}{$token}");
$url = "http://www.zhishengwh.com/api/Zspay/juhuiindex.html";
$urlData ="ACCOUNT={$account}&PAY_WAY={$pay_way}&BODY={$body}&NOTIFY_URL={$notify_url}&RETURN_URL={$return_url}&RESQN={$resqn}&PAY_AMOUNT={$pay_amount}&ZS_SIGN={$zs_sign}&CHANNE_TYPE={$channe_type}";
echo $this->request_ord($url,$urlData);
































//function resqnfun(){
//    return 'zspay'.date('Ymd').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
//}
//function getMilliseconds() {
//    list($microsecond , $time) = explode(' ', microtime()); //' '中间是一个空格
//    return (float)sprintf('%.0f',(floatval($microsecond)+floatval($time))*1000);
//}
//function hashsha1code($string,$key){
//    return hash_hmac("sha1",$string,$key);
//}
//
//function request_ord_pays($url,$params){
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
//    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
//    $headers = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
//    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//    $response = curl_exec($ch);
//    curl_close($ch);
//    return $response;
//}
//
//$url = "http://api.b4pay.hk/pay/aliSPay.do";  //上线地址
////$url = "http://api.sssx.red/pay/aliSPay.do"; //测试地址
//$key = "c33367701511b4f6020ec61ded352059";
//
//$payData['body'] = "testinfo";
//$payData['merchantId'] = "100000000000005";
//$payData['notifyUrl'] = "http://www.zhishengwh.com/api/Succes/info2";
//$payData['subject'] = "testtitle";
//$payData['timestamp'] = getMilliseconds();
//$payData['totalAmount'] = "1";
//$payData['tradeNo'] = resqnfun();//$resqnData;
//$buffx = "";
//foreach ($payData as $k => $v)
//{
//    if($k == 'notifyUrl'){
//        $v = urlencode($v);
//    }
//    $buffx .= $k . "=" . $v . "&";
//}
//$buffx = trim($buffx, "&");
//$signature = hashsha1code($buffx,$key);//签名
//$payData['signature'] = $signature;
//$buff = "";
//foreach ($payData as $k => $v)
//{
//    if($v != "" && !is_array($v)){
//        $buff .= $k . "=" . $v . "&";
//    }
//}
//$buff = trim($buff, "&");
//$resData = request_ord_pays($url,$buff);
//$arrData = json_decode($resData,true);
//header("location:".$arrData['data']['qr_code']);
//exit;





