<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class Promote extends Admin
{
    public function welcome(){
        return view("welcome");
    }
    public function mercode(){
        $merData = db("mercode")->select();
        $this->assign("merData",$merData);
        return view("mercode");
    }
    public function add_mercode(){
        if(request()->isPost()){
            $mercodedata = input("post.");
            $bool = db("mercode")->insert($mercodedata);
            $this->statusUrl($bool,"/admin/Promote/mercode","添加渠道成功");
        }else{
            return view("add_mercode");
        }
    }
    public function del_mercode(){
        $id = input("get.id");
        $bool = db("mercode")->delete($id);
        $this->statusUrl($bool,"/admin/Promote/mercode","删除渠道成功");
    }
}
