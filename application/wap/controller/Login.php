<?php
namespace app\wap\controller;
use app\common\controller\Wap;
class Login extends Wap{
    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';
    public function welcome(){
        if(request()->isPost()){
            $account = addslashes(input("post.account"));
            $password = $this->think_ucenter_md5(addslashes(input("post.password")), self::DATA_AUTH_KEY);
            $uc = db("user")->where(['account'=>$account,'password'=>$password])->find();
            $up = db("user")->where(['phone'=>$account,'password'=>$password])->find();
            if(!empty($uc) or !empty($up)){
                cookie("account",$account,3600);
                $a = $up['business_id'];
                $b = $uc['business_id'];
                $bid = $a??$b;
                setcookie("business_id",$bid,time()+3600);
                return $this->success("登录成功","/wap",2);
            }else{
                return $this->error("账号密码错误","/wap/login",2);
            }
        }else{
            return view("welcome");
        }
    }
    public function register(){
        return view("register");
    }
    public function forget_pwd(){
        return view("forget_pwd");
    }
    public function logout(){
        cookie('account', null);
        header("Location:/wap");
    }
    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
}