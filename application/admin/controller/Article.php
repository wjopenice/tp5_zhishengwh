<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class Article extends Admin
{
    public function welcome(){
        //发布文章
        $artc = db("article_log")->where(["status"=>"1","recover"=>"1"])->select();
        $this->assign("artc",$artc);
        return view("welcome");
    }
    public function status(){
        //审核文章
        $artc = db("article_log")->where(["status"=>"0","recover"=>"1"])->select();
        $this->assign("artc",$artc);
        return view("welcome");
    }
    public function recover(){
        //回收文章
        $artc = db("article_log")->where(["recover"=>"0"])->select();
        $this->assign("artc",$artc);
        return view("welcome");
    }
    public function add_artc(){
        if(request()->isPost()){
            $data['title'] = input("post.title");
            $data['author'] = (input("post.author") == "") ? "openice":input("post.author");
            $data['click'] = (input("post.click") == "") ? 300 :input("post.click");
            $data['source'] = (input("post.source") == "") ? "本站原创":input("post.source");
            $data['create_time'] = time();
            $data['content'] = htmlspecialchars(input("post.content"));
            $data['status'] = "0";
            $data['recover'] = "1";
            $bool = db("article_log")->insert($data);
            $this->statusUrl($bool,"/admin/Article/welcome","添加文章成功");
        }else{
            return view("add_artc");
        }
    }
    public function edit_artc(){
        if(request()->isPost()){
            $id = input("post.id");
            $data['title'] = input("post.title");
            $data['author'] = input("post.author");
            $data['click'] = input("post.click");
            $data['source'] = input("post.source");
            $data['create_time'] = time();
            $data['content'] = htmlspecialchars(input("post.content"));
            $data['status'] = "0";
            $data['recover'] = "1";
            $bool = db("article_log")->where(["id"=>$id])->update($data);
            $this->statusUrl($bool,"/admin/Article/welcome","修改文章成功");
        }else{
            $id = input("get.id");
            $artcone = db("article_log")->where(["id"=>$id])->find();
            $artcone['content'] = htmlspecialchars_decode($artcone['content']);
            $this->assign("artcone",$artcone);
            return view("add_artc");
        }
    }
    public function batch_artc(){
        $type = input("get.type");
        $bool = false;
        switch ($type){
            case "e":
                $arrData = explode (",",substr(input("get.id"),1));
                for($i=0;$i<count($arrData);$i++){ $bool = db("article_log")->where(["id"=>$arrData[$i]])->update(["recover"=>"0"]);}
                break; //放入回收站
            case "s":
                $arrData = explode (",",substr(input("get.id"),1));
                for($i=0;$i<count($arrData);$i++){ $bool = db("article_log")->where(["id"=>$arrData[$i]])->update(["status"=>"1"]);}
                break; //审核数据
            case "c":
                $arrData = explode (",",substr(input("get.id"),1));
                for($i=0;$i<count($arrData);$i++){ $bool = db("article_log")->where(["id" => $arrData[$i], "recover" => "0"])->delete();}
                break; //删除数据
            case "r":
                $arrData = explode (",",substr(input("get.id"),1));
                for($i=0;$i<count($arrData);$i++){ $bool = db("article_log")->where(["id" => $arrData[$i], "recover" => "0"])->delete();}
                break; //清空属性
        }
        $this->statusUrl($bool,"/admin/Article/welcome","操作成功");
    }
    public function one_artc(){
        $type = input("get.type");
        $id = input("get.id");
        $bool = false;
        switch ($type){
            case "e":
                $bool = db("article_log")->where(["id"=>$id])->update(["recover"=>"0"]);
                break; //放入回收站
            case "s":
                $bool = db("article_log")->where(["id"=>$id])->update(["status"=>"1"]);
                break; //审核数据
            case "c":
                $bool = db("article_log")->where(["id"=>$id,"recover"=>"0"])->delete();
                break; //删除数据
            case "r":
                $bool = db("article_log")->where(["id"=>$id])->update(["recover"=>"1"]);
                break; //恢复数据
        }
        $this->statusUrl($bool,"/admin/Article/welcome","操作成功");
    }
}
