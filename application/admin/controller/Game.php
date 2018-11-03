<?php
namespace app\admin\controller;
use app\common\controller\Admin;
class Game extends Admin
{
    public function welcome(){
        return view("welcome");
    }
    public function type(){
        $type = db("apk_type")->select();
        $this->assign("type",$type);
        return view("type");
    }
    public function typedel(){
        $id = input("get.id");
        $bool = db("apk_type")->where(['id'=>$id])->delete();
        $this->statusUrl($bool,"/admin/Game/type","删除分类成功");
    }
    public function typeadd(){
        if(request()->isPost()){
            $data['type'] = input("post.type");
            $bool = db("apk_type")->insert($data);
            $this->statusUrl($bool,"/admin/Game/type","添加分类成功");
        }else{
            return view("typeadd");
        }
    }
    public function apk(){
        $data = db("apk")->alias("a")->field("a.id,t.type,a.title,a.icon,a.addr_name,a.addr_code,a.info,a.pic")->join('apk_type t','t.id = a.t_id')->select();
        $this->assign("data",$data);
        return view("apk");
    }
    public function apkdel(){
        $id = input("get.id");
        $bool = db("apk")->delete($id);
        $this->statusUrl($bool,"/admin/Game/apk","删除apk成功");
    }
    public function apkadd(){
        if(request()->isPost()){
            $data = input("post.");
            $fileicon = input("file.icon");
            $filepic = input("file.pic");
            $filename = ROOT_PATH."/uploads/";
            // if(file_exists($filename)){ mkdir($filename,0777,true); }
            $icon = $fileicon->move($filename);
            $pic = $filepic->move($filename);
            if($icon && $pic){
                $data['icon'] = $icon->getSaveName();
                $data['pic'] = $pic->getSaveName();
                $bool = db("apk")->insert($data);
                $this->statusUrl($bool,"/admin/Game/apk","添加apk成功");
            }else{
                $err['erricon'] = $fileicon->getError();
                $err['errpic'] = $filepic->getError();
                $this->error($err);
                exit;
            }
        }else{
            $type = db("apk_type")->select();
            $this->assign('typex',$type);
            return view("apkadd");
        }
    }
    public function apkedit(){
        if(request()->isPost()){
            $data = input("post.");
            $fileicon = input("file.icon");
            $filepic = input("file.pic");
            $id = input("post.id");
            unset($data['id']);
            if(empty($fileicon) || empty($filepic)){
                $bool = db("apk")->where(['id'=>$id])->update($data);
                $this->statusUrl($bool,"/admin/Game/apk","修改apk成功");
            }else{
                $filename = ROOT_PATH."/uploads/";
                // if(file_exists($filename)){ mkdir($filename,0777,true); }
                $icon = $fileicon->move($filename);
                $pic = $filepic->move($filename);
                if($icon && $pic){
                    $data['icon'] = $icon->getSaveName();
                    $data['pic'] = $pic->getSaveName();
                    $bool = db("apk")->where(['id'=>$id])->update($data);
                    $this->statusUrl($bool,"/admin/Game/apk","修改apk成功");
                }else{
                    $err['erricon'] = $fileicon->getError();
                    $err['errpic'] = $filepic->getError();
                    $this->error($err);
                    exit;
                }
            }

        }else{
            $id = input("get.id");
            $type = db("apk_type")->select();
            $apk = db("apk")->where(['id'=>$id])->find();
            $this->assign(['typex'=>$type,'apk'=>$apk]);
            return view("apkedit");
        }
    }
    public function giftlist(){
        $arrData = db("mercode_gift")->select();
        $this->assign(["arrData"=>$arrData]);
        return view("giftlist");
    }
    public function giftlog(){
        $arrData = db("mercode_log")->select();
        $this->assign(["arrData"=>$arrData]);
        return view("giftlog");
    }
    public function giftdel(){
        $id = input("get.id");
        $bool = db("mercode_gift")->where(['id'=>$id])->delete();
        return $this->statusUrl($bool,"/admin/Game/giftlist.html","删除礼包成功","删除礼包失败","/admin/Game/giftlist.html",2);
    }
    public function giftlogdel(){
        $id = input("get.id");
        $bool = db("mercode_log")->where(['id'=>$id])->delete();
        return $this->statusUrl($bool,"/admin/Game/giftlog.html","删除礼包成功","删除礼包失败","/admin/Game/giftlog.html",2);
    }
    public function gift(){
        if(request()->isPost()){
            $data = input("post.");
            $arr = [];
            for($i=0;$i<count($data['gifttitle']);$i++){
                $arr['$info'][] = [$data['gifttitle'][$i],$data['giftinfo'][$i],$data['createtime'][$i],$data['endtime'][$i]];
            }
            unset($data['gifttitle']);
            unset($data['giftinfo']);
            unset($data['createtime']);
            unset($data['endtime']);
            $data['gamegift'] = json_encode($arr);
            $bool = db("mercode_gift")->insert($data);
            return $this->statusUrl($bool,"/admin/Game/giftlist.html","添加礼包成功","添加礼包失败","/admin/Game/gift.html",2);
        }else{
            $arrData = db("mercode")->select();
            $this->assign(["arrData"=>$arrData]);
            return view("gift");
        }
    }
}
