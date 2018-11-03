<?php
namespace app\api\controller;

use app\common\controller\Api;

class Succes extends Api{

    //广州银行
    const SIGN_KEY = 'z89mu331vh9jxhxeojy4o0w1okpe168c9w0q91t4'; //签名key
    const MERCHANT_NO = '10000001';//商户号
    const REQUEST_API = 'http://www.yspay.co/pay/api.php'; //请求地址
    //派易云
    const PY_ID = 8651;
    const PY_KEY = "Hq2NuVT8y6iTDcbZZ4PG1t0h1ltDqg22cW9Ja/SU+YMHtv2Lx5";
    const PY_URL = "http://open.pyicenter.com/appmerchantproxy";

    public function info(){
        $paydata = $_GET;
        recordlog("pay2.txt","success".json_encode($paydata,320));
        //商户订单号
        $out_trade_no = htmlspecialchars($paydata['out_trade_no']);
        //支付宝交易号
        $trade_no = htmlspecialchars($paydata['trade_no']);

        echo "交易成功<br />支付宝交易号：".$trade_no."商户订单号:".$out_trade_no;
        //交易成功
        //同步跳转下游商户
        sleep(3);
        header("location:http://www.zhishengwh.com/recharge?type=recharge");

//        $str = file_get_contents("./log/pay6.txt");
//        $boolstatus = db("promote_deposit")->where(['order_number'=>$str])->update(["pay_status"=>1]);
//        if($boolstatus){
//            $act = db("promote_deposit")->field("promote_account,actual_amount")->where(["order_number"=>$str])->find();
//            $cum = db("user")->field("cumulative")->where(["business_id"=>$act['promote_account']])->find();
//            $cumulative = $cum['cumulative'] + $act['actual_amount'];
//            $bool = db("user")->where(["business_id"=>$act['promote_account']])->update(["cumulative"=>$cumulative]);
//
//            echo db("user")->getLastSql();
//            if($bool){
//                //通知下游商户
//
//            }
//        }

    }

    public function info2(){
        $paydata = file_get_contents('php://input');
        $time = date("Y-m-d H:i:s",time());
        recordlog("pay2.txt","successinfo2{$time}".$paydata."successinfo2end");
        recordlog("pay7.txt",$paydata);
        $resdata = json_decode($paydata,true);
        if($resdata['trade_status'] == 'TRADE_SUCCESS'){
            recordlog("pay7.txt","1111");
            $boolstatus = db("promote_deposit")->where([ 'order_number'=>$resdata['out_trade_no'] ])->update(["pay_status"=>1]);
            if($boolstatus){
                $act = db("promote_deposit")->field("*")->where([ "order_number"=>$resdata['out_trade_no'] ])->find();
                $cum = db("user")->field("cumulative")->where(["business_id"=>$act['promote_account']])->find();
                $cumulative = $cum['cumulative'] + $act['actual_amount'];
                $bool = db("user")->where(["business_id"=>$act['promote_account']])->update(["cumulative"=>$cumulative]);
                if($bool){
                    for ($i=0;$i<3;$i++){
                        //通知下游商户成功
                        $request = str_replace("\\/", "/", json_encode([
                            'account'=>$act['promote_account'],
                            'resqn'=>$act['pay_order_number'],
                            'pay_way'=>$act['pay_order_number'],
                            'trade_status'=>$act['pay_order_number'],
                            'pay_amount'=>$act['pay_order_number'],
                            'trade_no'=>$act['pay_order_number']],JSON_UNESCAPED_UNICODE));
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $act['notify_url']);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length:' . strlen($request)));
                        curl_setopt($ch, CURLOPT_POSTFIELDS , $request);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $output = curl_exec($ch);
                        echo $output;
                        curl_close($ch);
                        recordlog("pay7.txt","sendsuccess".$request);
                        sleep(3);
                    }
                }
            }
        }else{
            //通知下游商户未支付
            recordlog("pay7.txt","222");
        }
    }

}