<?php
namespace app\mobile\controller;

use app\common\controller\Mobile;

class Gear extends Mobile{

    public function listx(){
        if(request()->isPost()){
            $id = input("post.id");
            $bool = db("promote_level")->where('id',$id)->delete();
            if($bool){
                return json(["msg"=>"ok"]);
            }else{
                return json(["msg"=>"error"]);
            }
        }else{
            $level = db("promote_level")->field("id,level,comm_revenue")->select();
            $this->assign("level",$level);
            return view("list");
        }
    }
    public function add(){
        if(request()->isPost()){
            $input = input("post.");
            $level = mb_substr($input['level'],0,-1);
            $input['ident'] = number_size_convert($level);
            $bool = db("promote_level")->insert($input);
            if($bool){
                $this->success("添加成功","/mobile/gear",2);
            }else{
                $this->error("添加失败");
            }
        }else{
            return view("add");
        }
    }
    public function detail(){
        $id = input("request.id");
        if(request()->isPost()){
            $input = input("post.");
            $level = mb_substr($input['level'],0,-1);
            $input['ident'] = number_size_convert($level);
            $bool = db("promote_level")->where("id",$id)->update($input);
            if($bool){
                $this->success("修改成功","/mobile/gear",2);
            }else{
                $this->error("修改失败");
            }
        }else{
            $data = db("promote_level")->where("id",$id)->find();
            $this->assign("data",$data);
            return view("detail");
        }
    }

}