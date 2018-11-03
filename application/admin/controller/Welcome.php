<?php
namespace app\admin\controller;

use think\Controller;
//异步公共文库
class Welcome extends Controller{

    public function ajax_status(){
        $id = input("post.i");
        $data = input("post.d");
        $type = input("post.t");
        $bool = db($type)->where(['id'=>$id])->update(['status'=>$data]);
        if($bool){
            return json(['status'=>"ok"]);
        }else{
            return json(['status'=>"error"]);
        }
    }

}