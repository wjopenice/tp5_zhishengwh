<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class Index extends Admin
{
    public function welcome(){
        return view("index");
    }
    public function info(){
        return view("info");
    }
    public function element(){
        return view("element");
    }
    public function pass(){
        return view("pass");
    }
}
