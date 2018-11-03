<?php
namespace app\wap\controller;
use app\common\controller\Wap;
class Index extends Wap{
    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';
    public function welcome(){
        $apk = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->limit(0,9)->select();
        $apk1 = db("apk")->limit(11,4)->select();
        $apk2 = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->limit(10,9)->select();
        $count = db("apk")->count();
        $apk3 = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->limit(5,9)->select();
        $count = db("apk")->count();
        //排行
        $randData = db("apk")
            ->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->limit(rand(0,$count-8),4)
            ->select();
        //广告位
        $zswh_adv = db("advert")->where(["status"=>1])->select();
        $this->assign([
            'apk'=>$apk,
            'apk1'=>$apk1,
            'apk2'=>$apk2,
            'apk3'=>$apk3,
            'randData'=>$randData,
            'zswh_adv'=>$zswh_adv
        ]);
        return view("welcome");
    }
    public function gmct(){
//        $type = db("apk_type")->select();
//        $strName = "apk";
        $arrData = [];
//        foreach ($type as $key=>$value){
//            $strStr = $strName.$value['id'];
//            $$strStr = db("apk")->where(['t_id'=>$value['id']])->select();
//            if(!empty($$strStr)){
//                $arr[0] = $value['type'];
//                $arr[1] = $$strStr;
//                $arrData[] = $arr;
//            }
//        }
        $this->assign("arrData",$arrData);
        return view("gmct");
    }
    public function info(){
        $id = input("get.id");
        $arrData = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->where(["a.id"=>$id])
            ->find();
        $apk1 = db("apk")->limit(11,4)->select();
        $apk2 = db("apk")->limit(0,4)->select();
        $this->assign(["arrData"=>$arrData,"apk1"=>$apk1,"apk2"=>$apk2]);
        return view("info");
    }
    public function search(){
        $data = preg_replace("/[%_\s]+/","",ltrim(addslashes(input("request.d"))));
        $arrData = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->where("a.title","like","%".$data."%")
            ->paginate(15);
        $page = $arrData->render();
        $this->assign(["arrData"=>$arrData,"page"=>$page]);
        return view("search");
    }
    public function gift(){
        $apk1 = db("apk")->limit(rand(0,17),3)->select();
        $apk3 = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->limit(5,9)->select();
        $zswh_adv = db("advert")->where(["status"=>1])->select();
        $this->assign(['apk1'=>$apk1,'apk3'=>$apk3,'zswh_adv'=>$zswh_adv ]);
        return view("gift");
    }
    public function customer(){
        if(cookie('account')){
            $user = cookie('account');
            $this->assign("user",$user);
            return view("customer");
        }else{
            return view("login/welcome");
        }
    }
    public function customer_logout(){
        $user = cookie('account');
        $userData = db("user")->where(["phone"=>$user])->find();
        $this->assign("userData",$userData);
        return view("customer_logout");
    }
    public function game_rank(){
        $apk1 = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->where('a.t_id',['=',2],['=',4],'or')
            ->limit(0,3)->select();
        $apk2 = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->where('a.t_id',['=',2],['=',4],'or')
            ->limit(3,15)->select();
        $this->assign("apk1",$apk1);
        $this->assign("apk2",$apk2);
        return view("game_rank");
    }
    public function game_gift(){
        $id = input("get.id");
        $arrData = db("apk")->alias("a")
            ->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")
            ->join('apk_type t','t.id = a.t_id')
            ->where(["a.id"=>$id])
            ->find();
        $this->assign(["arrData"=>$arrData]);
        return view("game_gift");
    }
    public function game_down(){
        return view("game_down");
    }
    public function about_us(){
        return view("about_us");
    }
    public function my_capital(){
        return view("my_capital");
    }
    public function record(){
        return view("record");
    }
    public function recharge(){
        return view("recharge");
    }
    public function msg(){
        $news = db("article_log")->where(["status"=>"1","recover"=>"1"])->order("id desc")->paginate(10);
        $page = $news->render();
        $this->assign(["news"=>$news,"page"=>$page]);
        return view("msg");
    }
    public function msg_info(){
        return view("msg_info");
    }
    public function modify_pwd(){
        if(request()->isAjax()){
            $user = cookie('account');
            $password = $this->think_ucenter_md5(addslashes(input("post.password")), self::DATA_AUTH_KEY);
            $newpass = $this->think_ucenter_md5(addslashes(input("post.newpass")), self::DATA_AUTH_KEY);
            $rwpass = $this->think_ucenter_md5(addslashes(input("post.repass")), self::DATA_AUTH_KEY);
            if($newpass <> $rwpass){
                return json(['msg'=>"两次密码不一致"]);
            }else{
                $userData = db("user")->where(['password'=>$password,"phone"=>$user])->find();
                if(!empty($userData)){
                    $bool = db("user")->where(["phone"=>$user])->update(['password'=>$newpass]);
                    if($bool){
                        cookie('account', null);
                        return json(['msg'=>"修改成功"]);
                    }else{
                        return json(['msg'=>"修改失败"]);
                    }
                }else{
                    return json(['msg'=>"原密码错误"]);
                }
            }
        }else{
            return view("modify_pwd");
        }
    }
    public function problem(){
        $type = input("get.type");
        $arrData = db("issue")->where(['type'=>$type])->select();
        $this->assign(["arrData"=>$arrData]);
        return view("problem");
    }
    public function problem_info(){
        $id = input("get.id");
        $oneData = db("issue")->where(['id'=>$id])->find();
        $type = $oneData['type'];
        $arrData = db("issue")->where("type",$type)->where("id","not in",$id)->select();
        $this->assign(["oneData"=>$oneData,"arrData"=>$arrData]);
        return view("problem_info");
    }
    public function problem_list(){
        $type = input("get.type");
        $arrData = db("issue")->where(['type'=>$type])->select();
        $this->assign(["arrData"=>$arrData]);
        return view("problem_list");
    }
    public function feedback(){
        if(request()->isAjax()){
            $data['content'] = htmlspecialchars(addslashes(input("post.d")));
            $data['create_time'] = time();
            $bool = db("feedback")->insert($data);
            if($bool){return json(["msg"=>"ok"]);}else{return json(["msg"=>"no"]);}
        }else{
            return view("feedback");
        }
    }
    public function ajax_gc(){
        $type = db("apk_type")->select();
        $strName = "apk";
        $arrData = [];
        foreach ($type as $key=>$value){
            $strStr = $strName.$value['id'];
            $$strStr = db("apk")->where(['t_id'=>$value['id']])->select();
            if(!empty($$strStr)){
                $arr[0] = $value['type'];
                $arr[1] = $$strStr;
                $arrData[] = $arr;
            }
        }
        return json_encode($arrData,320);
    }

    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
}