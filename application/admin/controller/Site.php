<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class Site extends Admin
{
    public function welcome(){
        return view("welcome");
    }
    public function basc(){
        if(request()->isPost()){
            $data = input("post.");
            $bool = db("site")->where(['id'=>input("get.id")])->update($data);
            $this->statusUrl(1,"/admin/Site/basc","修改信息成功");
        }else{
            $site = db("site")->find();
            $this->assign("site",$site);
            return view("basc");
        }
    }
    public function advert(){
        $advert = db("advert")
            ->alias("A")
            ->field("A.id,A.name,T.type,A.pic_url,A.addr,A.status")
            ->join("advert_type T","T.id = A.position")
            ->select();
        $this->assign("advert",$advert);
        return view("advert");
    }
    public function advertadd(){
        if(request()->isPost()){
            $data = input("post.");
            $fileicon = input("file.icon");
            $filename = ROOT_PATH."/uploads/";
            // if(file_exists($filename)){ mkdir($filename,0777,true); }
            $icon = $fileicon->move($filename);
            if($icon){
                $data['pic_url'] = $icon->getSaveName();
                $bool = db("advert")->insert($data);
                $this->statusUrl($bool,"/admin/Site/advert","添加广告成功");
            }else{
                $err['erricon'] = $fileicon->getError();
                $this->error($err);
                exit;
            }
        }else{
            $adv_type = db("advert_type")->select();
            $this->assign("adv_type",$adv_type);
            return view("advertadd");
        }
    }
    public function advertdel(){
        $id = input("get.id");
        $bool = db("advert")->where(["id"=>$id])->delete();
        $this->statusUrl($bool,"/admin/Site/advert","删除广告成功");
    }
    public function links(){
        $link = db("links")->select();
        $this->assign("link",$link);
        return view("links");
    }
    public function linkadd(){
        if(request()->isPost()){
            $data = input("post.");
            $bool = db("links")->insert($data);
            $this->statusUrl($bool,"/admin/Site/links","添加友链成功");
        }else{
            return view("linkadd");
        }
    }
    public function linkdel(){
        $id = input("get.id");
        $bool = db("links")->where(["id"=>$id])->delete();
        $this->statusUrl($bool,"/admin/Site/links","删除友链成功");
    }
}
