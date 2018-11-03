<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/12
 * Time: 17:51
 */
    namespace app\common\controller;
    use think\Controller;

    class Admin extends Controller {

        public function _initialize() {
            header('Content-type:text/html;charset=utf-8');
            $account = session('account');
            $depositx= db("promote_deposit")->where("pay_status",1)->order("id desc")->limit(0,100)->select();
            $total = count($depositx);
            $showList = 20;
            $totalPage = ceil($total/$showList);
            $urlpage = !empty(input("request.page"))?input("request.page"):1;
            $urllimit = !empty(input("request.limit"))?input("request.limit"):1;
            if(!empty($account)){
                $this->assign('account', $account);
                $this->assign("totalPage",$totalPage);
                $this->assign("showList",$showList);
                $this->assign("urlpage",$urlpage);
                $this->assign("urllimit",$urllimit);
            }else{
                $this->error("请登录","/admin",2);
            }
        }
    }