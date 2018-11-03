<?php
namespace app\api\controller;
use app\common\controller\Api;
class Respay extends Api{
    public function index(){
        $t = input("get.t");
        $arrData = db("promote_deposit")->where([ 'order_number'=>$t ])->find();
        if($arrData['pay_status'] == 1){
            return json(["code"=>1,"urlx"=>$arrData['return_url']]);
        }else{
            return json(["code"=>0]);
        }
    }
}