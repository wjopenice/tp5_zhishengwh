<?php
namespace app\index\controller;

use think\Controller;

class Login extends Controller{

    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';

    public function _initialize(){
        $site = db("site")->find();
        $zswh_adv = db("advert")->where(["status"=>1])->select();
        $link = db("links")->where(["status"=>1])->select();
        $this->assign("site",$site);
        $this->assign('zswh_adv',$zswh_adv);
        $this->assign('link',$link);
    }

    public function login(){
        if(request()->isPost()){
            $user = addslashes(input("post.username"));
            $pass = $this->think_ucenter_md5(addslashes(input("post.userpass")), self::DATA_AUTH_KEY);
            $uc = db("user")->where(['account'=>$user,'password'=>$pass])->find();
            $up = db("user")->where(['phone'=>$user,'password'=>$pass])->find();
            if(!empty($uc) or !empty($up)){
                $type = $_GET['login'];
                setcookie("account",$user,time()+3600,"/",".zhishengwh.com");
                if($type == "shop"){
                    return $this->success("登录成功","http://shop.zhishengwh.com",2);
                }else{
                    $a = $up['business_id'];
                    $b = $uc['business_id'];
                    $bid = $a??$b;
                    setcookie("business_id",$bid,time()+3600);
                    return $this->success("登录成功","/index",2);
                }
            }else{
                $type = $_GET['login'];
                if($type == "shop"){
                    return $this->error("账号密码错误","/index/Login/login?login=shop",2);
                }else{
                    return $this->error("账号密码错误","/index/Login/login",2);
                }
            }
        }else{
            return view("login");
        }
    }

    public function forget_pwd(){
        return view("forget_pwd");
    }

    public function register(){
        if(request()->isPost()){
            $data['account'] = input("post.phone");
            $data['password'] = $this->think_ucenter_md5(addslashes(input("post.password")), self::DATA_AUTH_KEY);
            $data['nickname'] = mershuffle("zspay_",20);
            $data['phone'] =  $data['account'];
            $data['real_name'] = input("post.real_name");
            $data['idcard'] = input("post.idcard");
            $data['register_time'] = time();
            $data['register_ip'] = getip();
            $data['promote_id'] = 1;
            //注册商户号
            $strkey = "zswh".mershuffle("zswh",20);
            $data['business_id'] = $strkey;
            //注册token
            $privary = file_get_contents('rsa_private_key.pem');
            $pkeyid = openssl_pkey_get_private($privary);
            openssl_sign($strkey, $signature, $pkeyid);
            $token= sha1(base64_encode($signature));
            $data['token'] = $token;
            //写入数据库
            $bool = db("user")->insert($data);
            //开通商户支付功能
            $account_data['account'] = $strkey;
            $account_data['nickname'] = $data['nickname'];
            $account_data['mobile_phone'] = $data['phone'];
            $account_data['comm_type'] = "T1";
            $account_data['level_id'] = 1;
            $account_data['referee_id'] = 1;
            $account_data['password'] = $data['password'];
            $account_data['hands_pass'] = $data['password'];
            $account_data['paykey'] = $token;
            $account_data['identity'] = 1;
            $account_data['create_time'] = time();
            db("promote")->insert($account_data);
            if(input("post.type") == 'pc'){
                $type = $_GET['login'];
                if($type == "shop"){
                    return $this->statusUrl($bool,"/index/Login/login?login=shop","注册成功","注册失败","/index/Login/register?login=shop",2);
                }else{
                    return $this->statusUrl($bool,"/index/Login/login","注册成功","注册失败","/index/Login/register",2);
                }
            }else{
                return $this->statusUrl($bool,"/wap/login.html","注册成功","注册失败","/wap/register.html",2);
            }
        }else{
            return view("register");
        }
    }

    public function logout(){
        setcookie("account", null, time() - 3600, "/", ".zhishengwh.com");
        $type = empty($_GET['login'])?"www":$_GET['login'];
        if($type == "shop"){
            header("Location:http://shop.zhishengwh.com");
        }else{
            header("Location:/index");
        }
    }

    public function loginajax(){
        $data = addslashes(input("post.d"));
        $arrData = db("user")->where(['account'=>$data])->find();
        if(!empty($arrData)){
            //cookie("account",$data,3600);
            return 1;
        }else{
            return 0;
        }
    }
    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

}