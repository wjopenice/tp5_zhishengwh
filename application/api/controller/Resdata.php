<?php
namespace app\api\controller;

use app\common\controller\Api;

class Resdata extends Api{

    public function index(){

         $str = file_get_contents("./log/pay6.txt");
         $boolstatus = db("promote_deposit")->where(['order_number'=>$str])->update(["pay_status"=>1]);
         if($boolstatus){
             $act = db("promote_deposit")->field("promote_account,actual_amount")->where(["order_number"=>$str])->find();
             $cum = db("user")->field("cumulative")->where(["business_id"=>$act['promote_account']])->find();
             $cumulative = $cum['cumulative'] + $act['actual_amount'];
             $bool = db("user")->where(["business_id"=>$act['promote_account']])->update(["cumulative"=>$cumulative]);
             if($bool){
                 //通知下游商户
             }
         }
         $resdata = $_REQUEST;
         $click = "{'click':'1'}";
         recordlog("pay2.txt","click".json_encode($click,320));
         recordlog("pay2.txt","ajaxdata".json_encode($resdata,320));
         exit;
    }

    public function index2(){
        $resdata = $_REQUEST;
        recordlog("pay2.txt","ajaxdata".json_encode($resdata,320));
        exit;
    }
}