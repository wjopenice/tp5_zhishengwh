<?php
namespace app\index\controller;
use app\common\controller\Home;

use first\second\Modeli;
use first\second\Page;

class Index extends Home {
    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';
    public function welcome(){
        if(isMobile()){
            header("Location:/wap.html");
        }else{
            $apk = db("apk")->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
                ->join('apk_type t','t.id = a.t_id')
                ->where('a.t_id',['=',2],['=',4],'or')
                ->limit(0,9)->select();
            $apk1 = db("apk")->limit(9,10)->select();
            $host = db("apk")->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
                ->join('apk_type t','t.id = a.t_id')
                ->where('a.t_id',['>',0],['<',20],'and')
                ->limit(rand(0,20),4)->select();
            //新闻
            $news = db("article_log")->where(["status"=>"1","recover"=>"1"])->order("id desc")->limit(0,2)->select();
            $this->assign([
                'apk'=>$apk,
                'apk1'=>$apk1,
                'host'=>$host,
                'news'=>$news
            ]);
            return view("welcome");
        }
    }
    public function gmct(){
        if(!empty(input("request.t"))){
            $data = input("request.d");
            $type = input("request.t");
            $p = input("request.page");
            $showpage = 15;
            $start = ($p-1)*$showpage;
            $apk = db("apk")
                ->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
                ->join('apk_type t','t.id = a.t_id')
                ->where("t.".$type,"like","%".$data."%")
                ->paginate([$start,$showpage]);
            $page = $apk->render();
        }else{
            $apk = db("apk")
                ->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
                ->join('apk_type t','t.id = a.t_id')
                ->order("a.id desc")
                ->paginate(15);
            $page = $apk->render();
        }
        $type = db("apk_type")->select();
        $this->assign(['apk'=>$apk,'page'=>$page,'typex'=>$type]);
        return view("gmct");
    }
    public function gmct_detail(){
        $id = input("get.id");
        $apz = db("apk")
            ->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.info,a.pic,a.addr_code")
            ->join('apk_type t','t.id = a.t_id')
            ->where(["a.id"=>$id])
            ->find();
        $apz['addr_url'] = $apz['addr_code'];
        //生成二维码
        $codename = substr($apz['addr_name'],0,-4).".png";
        $this->qrcode($apz['addr_code'],$codename);
        $apz['addr_code'] = $codename;
        //礼包
        $gift = db("mercode_gift")->where(["id"=>1])->find();
        $gift['gamegift'] = json_decode($gift['gamegift'],true);
        //检测用户是否已经领取礼包
        if(cookie('account')){
            $user = cookie('account');
            $giftArr = db("mercode_log")->field("gamegift")->where(["gameid"=>"{$user}","gamename"=>"{$gift['gamename']}"])->select();
            if(!empty($giftArr)){
                $newData = [];
                foreach ($giftArr as $k=>$v){
                    $newData[] = $v['gamegift'];
                }
                $this->assign(["apz"=>$apz,"gift"=>$gift,"giftArr"=>$newData]);
                return view("gmct_detail");
            }else{
                $this->assign(["apz"=>$apz,"gift"=>$gift]);
                return view("gmct_detail");
            }
        }else{
            //输出
            $this->assign(["apz"=>$apz,"gift"=>$gift]);
            return view("gmct_detail");
        }
    }
    public function gmctajax(){
        $data = str_replace(" ","",input("request.d"));

        if(request()->isPost()){
            if($data == "角色"){
                $data .= "扮演";
            }else{
                $data .= "游戏";
            }
        }

        $arrData = db("apk")
            ->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code")
            ->join('apk_type t','t.id = a.t_id')
            ->where(["t.type"=>$data])
            ->paginate(15,false,['path'=>"",'query'=>['d'=>$data,'t'=>"type"]]);
        $page = $arrData->render();
        return json(['page'=>$page,"datastr"=>$arrData]);
    }
    public function search(){
        $data = preg_replace("/[%_\s]+/","",ltrim(addslashes(input("request.d"))));
        $apk = db("apk_type")->select();
        if(!empty(input("request.t"))){
            $type =  input("request.t");
            $p = input("request.page");
            $showpage = 15;
            $start = ($p-1)*$showpage;
            $arrData = db("apk")
                ->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic,a.t_id")
                ->join('apk_type t','t.id = a.t_id')
                ->where("t.".$type,"like","%".$data."%")
                ->paginate([$start,$showpage]);
            $page = $arrData->render();
        }else{
            if($data == ""){$data = "*****"; }
            $arrData = db("apk")
                ->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic,a.t_id")
                ->join('apk_type t','t.id = a.t_id')
                ->where("a.title","like","%".$data."%")
                ->paginate(15);
            $page = $arrData->render();
        }
        $this->assign(['page'=>$page,"datastr"=>$arrData,'apk'=>$apk]);
        return view("search");
    }
    public function news(){
        $news = db("article_log")->where(["status"=>"1","recover"=>"1"])->order("id desc")->paginate(10);
        $page = $news->render();
        $this->assign(["news"=>$news,"page"=>$page]);
        return view("news");
    }
    public function customer(){
        return view("customer");
    }
    public function customerinfo(){
        $issue_type = db("issue_type")->select();
        $issue = db("issue")->where(["type"=>1])->select();
        $this->assign(["issue_type"=>$issue_type,"issue"=>$issue]);
        return view("customerinfo");
    }
    public function newsinfo(){
        $id = input("get.id");
        $artcdata = db("article_log")->where(['id'=>$id])->find();
        $artcdata['content'] = htmlspecialchars_decode($artcdata['content']);
        $this->assign(["artcdata"=>$artcdata]);
        if(isMobile()){
            return view("newsinfo_wap");
        }else{
            return view("newsinfo");
        }
    }
    public function gift(){
        $apk = db("apk")
                ->alias("a")
                ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
                ->join('apk_type t','t.id = a.t_id')
                ->paginate(15);
        $page = $apk->render();
        $this->assign(['apk'=>$apk,'page'=>$page]);
        return view("gift");
    }
    public function convert(){
        return view("convert");
    }
    public function recharge(){
        if(cookie('account')){
            $user = cookie('account');
            $arrData = db("user")->field("business_id")->where(['phone'=>$user])->find();
            $this->assign("business_id",$arrData['business_id']);
            return view("recharge");
        }else{
            return $this->error("请先登录","/index/Login/login");
        }
    }
    public function uc(){
        if(cookie('account')){
            //获取会员信息
            $user = cookie('account');
            $arrData = db("user")->field("*")->where(['phone'=>$user])->find();
            //获取会员交易记录
            $pay_data = db("promote_deposit")->field("*")->where(['promote_account'=>$arrData['business_id']])->order("id desc")->paginate(10,false,['fragment'=>2]);
            $page = $pay_data->render();
            //充值成功总额
            $pay_success_total = $arrData['cumulative'];
            //企业信息
            $company = db("buss")->field("*")->where(['business_id'=>$arrData['business_id']])->find();
            $this->assign(["arrData"=>$arrData,'pay_data'=>$pay_data,'page'=>$page,'pay_success_total'=>$pay_success_total,'company'=>$company]);
            return view("uc");
        }else{
            return $this->error("请先登录","/index/Login/login");
        }
    }
    public function ajaxpwd(){
        $type = input("get.type");
        $user = cookie('account');
        if($type == 'user'){
            //身份验证
            $n = input("get.n");
            $c = input("get.c");
            $arr = db("user")->where(["real_name"=>$n,'idcard'=>$c,"phone"=>$user])->find();
            if(!empty($arr)){
                return json(["code"=>1]);
            }else{
                return json(["code"=>0]);
            }
        }else{
            //修改密码
            $pass = $this->think_ucenter_md5(addslashes(input("get.p")), self::DATA_AUTH_KEY);
            $bool = db("user")->where(["phone"=>$user])->update(['password'=>$pass]);
            if($bool){
                cookie('account', null);
                return json(["code"=>1]);
            }else{
                return json(["code"=>0]);
            }
        }
    }
    public function ajaxissue(){
        $id = input("get.id");
        $asynis = db("issue")->field("title,info")->where(["type"=>$id])->select();
        return json($asynis);
    }
    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
    public function ajaxgift(){
        $data['gameid'] = input("get.u");
        $data['gamename'] = input("get.g");
        $data['gamegift'] = input("get.f");
        db("mercode_log")->insert($data);
    }
}
