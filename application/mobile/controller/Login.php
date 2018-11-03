<?php
namespace app\mobile\controller;

use think\Controller;

class Login extends Controller
{
    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';

    public function welcome(){
        if(request()->isPost()){
            $account  = input('post.account');
            $password = input('post.password');
            $map['account'] = $account;
            /* 获取用户数据 */
            $user = db('hands_admin')->where($map)->find();
            if(is_array($user)){
                $userpass = $user['pass'];
                $psss = $this->think_ucenter_md5($password, self::DATA_AUTH_KEY);
                if($psss === $userpass){
                    session('account', $user);
                    $this->success("登录成功","/mobile/index",2);
                } else {
                    $this->error("密码错误","/mobile",2);
                }
            } else {
                $this->error("账号不存在","/mobile",2);
            }
        }else{
            return view("welcome");
        }
    }

    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    //退出
    public function logout() {
        session(null);
        $this->redirect('/mobile');
    }
}
