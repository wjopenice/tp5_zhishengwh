<?php
namespace app\pay\controller;

use think\Controller;

class Login extends Controller{
    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';

    public function login(){
        if($_POST){
            $account  = input('post.account');
            $password = input('post.password');
            $map['account'] = $account;

            /* 获取用户数据 */
            $user = db('hands_admin')
                ->where($map)
                ->find();
            if($user['status'] == 2){
                echo json_encode([
                        'msg'     => '此账号已禁用',
                        'error' => '1'
                    ]
                );
                exit;
            }
            if($user['status'] == 2){
                echo json_encode([
                        'msg'     => '该角色已禁用',
                        'error' => '1'
                    ]
                );
                exit;
            }
            $role = db('hands_role_config')->where('rid',$user['roleId'])->find();
            if($role['top_id'] == '' && $role['f_menu_id'] == '' && $user['grade'] == 0){
                echo json_encode([
                        'msg'     => '该帐号没有权限',
                        'error' => '1'
                    ]
                );
                exit;
            }

            if(is_array($user)){
                /* 验证用户密码 */
                if(($this->think_ucenter_md5($password, self::DATA_AUTH_KEY)) === $user['pass']){
                    rwLog(array(
                        'content' => '登录商户平台',
                        'login_ip' => get_server_ip(),
                        'time'   => time(),
                        'pid'   => $user['id'],
                        'identity' => 2
                    ));
                    $lifetime           = 1800;
                    session_set_cookie_params($lifetime);
                    unset($user['pass']);
                    session('account', $user);
                    session('isLogin', 1);
                    echo json_encode([
                            'msg'     => '登录成功',
                            'success' => '1'
                        ]
                    );
                    exit;
                } else {
                    //return -2; //密码错误
                    echo json_encode([
                            'msg'     => '密码错误',
                            'error' => '1'
                        ]
                    );
                    exit;
                }
            } else {
                //return -1; //账号不存在
                echo json_encode([
                        'msg'     => '账号不存在',
                        'error' => '1'
                    ]
                );
            }

        }else{
            return view("login");
        }
    }

    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    //退出
    public function logout() {
        session(null);
        $this->redirect('/admin');
    }
}