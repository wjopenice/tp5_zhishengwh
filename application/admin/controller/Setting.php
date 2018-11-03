<?php
namespace app\admin\controller;

use app\common\controller\Admin;

class Setting extends Admin
{
    public function welcome(){
        return view("welcome");
    }
    public function issue_type(){
        $issue_type = db("issue_type")->select();
        $this->assign("issue_type",$issue_type);
        return view("issue_type");
    }
    public function issue(){
        $issue = db("issue")->alias("i")->field("i.id,t.type,i.title,i.info")->join('issue_type t','t.id = i.type')->select();
        $this->assign("issue",$issue);
        return view("issue");
    }
    public function issue_add(){
        if(request()->isPost()){
            $issuedata = input("post.");
            $bool = db("issue")->insert($issuedata);
            $this->statusUrl($bool,"/admin/Setting/issue","添加问题成功");
        }else{
            $issue_type = db("issue_type")->select();
            $this->assign("issue_type",$issue_type);
            return view("issue_add");
        }
    }
    public function issue_del(){
        $id = input("get.id");
        $bool = db("issue")->delete($id);
        $this->statusUrl($bool,"/admin/Setting/issue","删除问题成功");
    }
    public function links(){
        $links = db("links")->order("id desc")->select();
        $this->assign("link",$links);
        return view("links");
    }
    public function addlinks(){
        if(request()->isPost()){
            $linkdata = input("post.");
            $linkdata['create_time'] = time();
            $bool = db("links")->insert($linkdata);
            $this->statusUrl($bool,"/admin/Setting/links","添加链接成功");
        }else{
            return view("addlinks");
        }
    }
    public function dellinks(){
        $id = input("get.id");
        $bool = db("links")->delete($id);
        $this->statusUrl($bool,"/admin/Setting/links","删除链接成功");
    }
    public function feedback(){
        $arr = db("feedback")->select();
        $this->assign("arr",$arr);
        return view("feedback");
    }

}
