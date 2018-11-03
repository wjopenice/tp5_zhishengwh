<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class App extends Admin
{
    public function welcome(){
        return view("welcome");
    }
    public function product_list(){
        $data = db("apk_shop")->field("*")->paginate(15);
        $show = $data->render();
//        foreach ($data as $k1=>$v1){
//            $nameselect = db("apk")->field("title")->where(["id" => $v1['name_select']])->find();
//            $data[$k1]['name_select'] = $nameselect['title'];
//        }
        $this->assign(["data"=>$data,"show"=>$show]);
        return view("product_list");
    }
    public function product_review(){
        $data = db("apk_shop")->field("*")->where(["status"=>"0"])->select();
//        foreach ($data as $k1=>$v1){
//            $nameselect = db("apk")->field("title")->where(["id" => $v1['name_select']])->find();
//            $data[$k1]['name_select'] = $nameselect['title'];
//        }
        $this->assign("data",$data);
        return view("product_review");
    }
    public function product_del(){
        $id = input("get.id");
        $bool = db("apk_shop")->where(["id"=>$id])->delete();
        $this->statusUrl($bool,"/admin/App/product_list","删除信息成功");
    }
    public function shop_review(){
        $type = input("get.type");
        $id = input("get.id");
        if($type == 'ok'){
            $bool = db("apk_shop")->where(["id"=>$id])->update(["status"=>'1']);
        }else{
            $bool = db("apk_shop")->where(["id"=>$id])->update(["status"=>'2']);
        }
        $this->statusUrl($bool,"/admin/App/product_review","审核信息完成");
    }
}
