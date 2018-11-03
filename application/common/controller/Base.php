<?php
    /**
     *  Author : Dream <34650064@QQ.com>
     *  Encoding : UTF-8
     *  Separator : Unix and OS X (\n)
     *  File Name : Base.php
     *  Create Date : 2017/6/2 10:07
     *  Version : 0.1
     *  Email Address : yxly330@126.com
     */

    namespace app\common\controller;

    use think\Config;
    use think\Controller;
    use think\Route;

    class Base extends Controller {
        protected $nodeUrl;
        protected $blackListRule = [
            'index/login/index',
            'mobile'
        ];

        public function _initialize() {
            header('Content-type:text/html;charset=utf-8');
            if (!is_file(DATA_PATH . 'installed.lock')) {
                $this->redirect('install/index/index');
            }
            if(session('isLogin') != 1){
                $this->redirect('login/login');
            }else{
                $id = session('promote_auth')['id'];
                $map['promote_id'] = $id;
                $map['pay_type'] = ['eq',2];
                $T0_data = db('promote_deposit')->where($map)->find();
                if($T0_data){
                    $this->assign('is_to',1);
                }else{
                    $this->assign('is_to',0);
                }

                $sub_map['promote_id'] = $id;
                $sub_map['pay_type'] = ['eq',9];
                $sub_data = db('promote_deposit')->where($sub_map)->find();
                if($sub_data){
                    $this->assign('is_sub',1);
                }else{
                    $this->assign('is_sub',0);
                }

                $this->assign('promote_auth',session('promote_auth'));
            }
        }
    }