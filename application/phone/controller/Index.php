<?php
namespace app\phone\controller;

use think\Controller;

class Index extends Controller
{
    public function welcome(){
        //echo "test welcome";
        return view("welcome");
    }
    public function test(){
        echo "test ok";
    }
}
