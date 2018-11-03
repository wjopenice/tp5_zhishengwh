<?php
namespace app\index\controller;
use think\Controller;
class Outforward extends Controller {

    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';

//    public function _initialize(){
//        if(cookie('account')){
//            //获取会员信息
//            $user = cookie('account');
//            $arrData = db("user")->field("*")->where(['phone'=>$user])->find();
//            //充值成功总额
//            $pay_success_total = db("promote_deposit")->where(['promote_account'=>$arrData['business_id'],'pay_status'=>1])->sum("pay_amount");
//            $this->assign(["arrData"=>$arrData,'pay_success_total'=>$pay_success_total]);
//        }else{
//            return $this->error("请先登录","/put_login.html");
//        }
//    }
    //登录
    public function put_login(){
        if(request()->isPut()){
            $user = trim(addslashes(input("account")));
            $pass = $this->think_ucenter_md5(addslashes(input("put.password")), self::DATA_AUTH_KEY);
            $uc = db("user")->where(['business_id'=>$user,'password'=>$pass])->find();
            if(!empty($uc)){
                cookie("account",$uc['phone'],3600);
                $bid = $uc['business_id'];
                setcookie("business_id",$bid,time()+3600);
                return $this->success("登录成功","/forward.html",2);
            }else{
                return $this->error("账号密码错误","/put_login.html",2);
            }
        }else{
            return view("put_login");
        }
    }
    public function put_logout(){
        cookie('account', null);
        header("Location:/forward.html");
    }
    //提现申请
    public function put_forward(){
        if(cookie("account")){
            if(request()->isPost()){
                $data = input("post.");
                $data['forward_time'] = time();
                $data['forward_status'] = "0";
                $bool = db("forward_log")->insert($data);
                return $this->statusUrl($bool,"/forward/list.html","提现申请成功","提现申请失败","/forward",2);
            }else{
                //获取会员信息
                $user = cookie('account');
                $arrData = db("user")->field("*")->where(['phone'=>$user])->find();
                //充值成功总额
                $pay_success_total = $arrData['cumulative'];
                $this->assign(["arrData"=>$arrData,'pay_success_total'=>$pay_success_total]);
                return view("put_forward");
            }
        }else{
            return $this->error("请登录","/put_login.html",2);
        }
    }
    //订单查询
    public function put_select(){
        if(cookie("account")){
            if(request()->isPost()){
                $d = preg_replace("/[%_\s]+/","",ltrim(addslashes(input("d"))));
                $pay_data = db("promote_deposit")->field("order_number,promote_account,pay_amount,	create_time,pay_status")->where(['order_number'=>$d])->find();
                return json_encode($pay_data,320);
            }else{
                return view("put_select");
            }
        }else{
            return $this->error("请登录","/put_login.html",2);
        }
    }
    //提现申请列表
    public function put_list(){
        if(cookie("account")){
            $user = cookie('account');
            $arrData = db("user")->field("*")->where(['phone'=>$user])->find();
            $bank = db("forward_log")->where(["account"=>$arrData['business_id'],"forward_status"=>0])->select();
            $this->assign("bank",$bank);
            return view("put_list");
        }else{
            return $this->error("请登录","/put_login.html",2);
        }
    }
    //提现成功列表
    public function put_success(){
        if(cookie("account")){
            $user = cookie('account');
            $arrData = db("user")->field("*")->where(['phone'=>$user])->find();
            $bank = db("forward_log")->where(["account"=>$arrData['business_id'],"forward_status"=>1])->select();
            $this->assign("bank",$bank);
            return view("put_list");
        }else{
            return $this->error("请登录","/put_login.html",2);
        }
    }
    //充值记录
    public function put_recharge(){
        if(cookie("account")){
            //获取会员信息
            $user = cookie('account');
            $arrData = db("user")->field("*")->where(['phone'=>$user])->find();
            //获取会员交易记录
            $pay_data = db("promote_deposit")->field("*")->where(['promote_account'=>$arrData['business_id']])->order("id desc")->paginate(10,false,['fragment'=>2]);
            $page = $pay_data->render();
            $this->assign(["pay_data"=>$pay_data,"page"=>$page]);
            return view("put_recharge");
        }else{
            return $this->error("请登录","/put_login.html",2);
        }

    }
    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

}