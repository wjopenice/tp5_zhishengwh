<?php
namespace app\common\controller;

use think\Controller;

class Home extends Controller{
    public function _initialize(){
        //热点
        $host = db("apk")->limit(0,5)->order("id desc")->select();
        $count = db("apk")->count();
        //排行
        $randData = db("apk")
            ->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->limit(rand(0,$count-8),7)
            ->select();
        //站点信息
        $site = db("site")->find();
        //广告位
        $zswh_adv = db("advert")->where(["status"=>1])->select();
        //友链
        $link = db("links")->where(["status"=>1])->select();
        $this->assign("site",$site);
        $this->assign('host',$host);
        $this->assign('randData',$randData);
        $this->assign('zswh_adv',$zswh_adv);
        $this->assign('link',$link);
    }

    public function qrcode($val="",$url=""){
        $path = ROOT_PATH . 'public' . DS . 'qrcode/';
        $saveurl = $path.$url;
        vendor('phpqrcode.phpqrcode');//引入插件类
        $errorCorrentionLevel = 'L'; //容错级别
        $matrixPoinSize = 4; //生成图片大小 //生成二维码,第二个参数为二维码保存路径
        \QRcode::png($val,$saveurl,$errorCorrentionLevel,$matrixPoinSize,2);
    }
}