<?php
namespace app\pay\controller;

use app\common\controller\Pay;

class Index extends Pay
{
    public function index(){
        return view("index");
    }

    public function welcome(){

        $data = getUserData();
        //最新公告
        $notice = db('hands_notice')->order('id desc')->select();

        //登录记录
        $logs = db('promote_logs')
            ->alias('l')
            ->field('l.*,p.account')
            ->join('hands_admin p','p.id=l.pid')
            ->where("l.identity=2")
            ->limit('8')
            ->order('l.id desc')
            ->select();
        foreach ($logs as $key=>$val) {
            $logs[$key]['time'] = date('Y-m-d H:i:s',$val['time']);
        }

        //快捷操作
        $quick = db('hands_menu')->where('quick',0)->select();

        $this->assign('data',$data);
        $this->assign('notice',$notice);
        $this->assign('logs',$logs);
        $this->assign('quick',$quick);

        return view("welcome");
    }

}
