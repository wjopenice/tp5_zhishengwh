<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/12
 * Time: 17:51
 */

    namespace app\common\controller;

    use think\Config;
    use think\Controller;
    use think\Route;

    class Pay extends Controller {
        protected $nodeUrl;
        protected $blackListRule = [
            'index/login/index',
            'mobile'
        ];

        public function _initialize() {
            header('Content-type:text/html;charset=utf-8');
            if(session('isLogin') != 1){
                if(isMobile()){
                    header("Location:/mobile");
                    exit;
                }else{
                    $this->redirect('/pay/login.html');
                }
            }else{
                $account = session('account');
                $id = $account['roleId'];
                $this->assign('account', $account);

                if (intval($account['grade']) === 1) {
                    //超级管理员，读取当前所有目录
                    $result = $this->getMenuList();

                } else if (intval($account['grade']) === 0) {
                    //普通管理员
                    //先判断顶级栏目是否有权限
                    $menuConfig = db('hands_role_config')->where('`rid`=' . $id)->find();
                    if (!empty($menuConfig)) {
                        $top = explode(',', $menuConfig['top_id']);
                        if (empty($top)) {
                            $this->error('对不起，你没有权限');
                        } else {
                            //读取相应的一级目录和二级目录
                            $result = $this->getMenuListForOrd($menuConfig, intval($id));
                        }
                    } else {
                        $this->error('你没有任何权限操作，请联系超级管理员给你设置权限');
                    }
                } else {
                    $this->error('无权限操作');
                }
                if (empty($result)) {
                    $this->error('没有目录');
                }
            }

            $this->assign('menu', $result);
        }

        //管理员获取所有目录
        public function getMenuList(){
            $menu = db('hands_menu')->where('`praendid`=0 and `hide` = 0')->order('solf asc')->select();
            $data = [];
            //[['title'=>$title,'icon'=>$icon,'spread'=>true,'children'=>[['title'=>$title,'icon'=>$icon,'url'=>$url],['title'=>$title,'icon'=>$icon,'url'=>$url]]]];
            if (!empty($menu)) {
                foreach ($menu as $k => $v) {
                    if ($k === 0) {
                        $spread = true;
                    }
                    $children = [];
                    $spread   = $k === 0 ? true : false;
                    $row      = db('hands_menu')->where('`praendid`=' . $v['id'] . ' and hide = 0')->order('solf asc')->select();
                    if (!empty($row)) {
                        //二级导航
                        foreach ($row as $_k => $_v) {
                            array_push($children, [
                                    'title' => $_v['title'],
                                    'faicon'  => $_v['faicon'],
                                    'faicon2'  => $_v['faicon2'],
                                    'url'  => $_v['url'],
                                ]
                            );
                        }
                        array_push($data, [
                                'title'    => $v['title'],
                                'faicon'     => $v['faicon'],
                                'faicon2'    => $v['faicon2'],
                                'spread'   => $spread,
                                'children' => $children
                            ]
                        );
                        unset($children);
                    } else {
                        //一级导航
                        array_push($data, [
                                'title'  => $v['title'],
                                'faicon'   => $v['faicon'],
                                'faicon2'  => $v['faicon2'],
                                'spread' => $spread,
                                'url'   => $v['url'],
                                'children' => ''
                            ]
                        );
                    }
                }
            }
            return $data;
        }

        //读取普通管理员的目录
        public function getMenuListForOrd($arr,$id){
            if (!is_array($arr) || !is_int($id)) {
                return null;
            }
            //先将当前一级目录取出
            $row  = db('hands_menu')->where("id in (".$arr['top_id'].") and hide=0")->select();
            foreach ($row as $k=>$v) {
                $row[$k]['children'] = db('hands_menu')->where("praendid = ".$v['id']." and hide=0 and id in (".$arr['f_menu_id'].")")->select();
            }
            return $row;
        }
    }