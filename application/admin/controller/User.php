<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class User extends Admin
{
    public function welcome(){
        return view("welcome");
    }
    public function userx(){
        $data = db("user")->select();
        $this->assign("data",$data);
        return view("userx");
    }
    public function userdel(){
        $id = input("get.id");
        $bool = db("user")->delete($id);
        $this->statusUrl($bool,"/admin/User/userx","删除user成功");
    }
}
