<?php
namespace app\mobile\controller;
use app\common\controller\Mobile;
class Recharges extends Mobile{

    public function listx(){
        return view("list");
    }
    public function getlist(){
        $page = input("get.page");
        $showlist = input("get.limit");
        $startPage = ($page-1)*$showlist;
        $deposit= db("promote_deposit")->field("id,promote_account,pay_order_number,create_time,pay_status")->order("id desc")->limit($startPage,$showlist)->select();
        foreach ($deposit as $key=>$value){
            $deposit[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);
            if($deposit[$key]['pay_status'] == 1){
                $deposit[$key]['pay_status'] = "支付成功";
            }else{
                $deposit[$key]['pay_status'] = "待支付";
            }
        }
        return json($deposit);
    }

    public function detail(){
        $id = input("get.id");
        $data = db("promote_deposit")->where("id",$id)->find();
        $level_id = db('promote_deposit')->alias('pd')->field('p.level_id')->join('promote p','p.id=pd.promote_id')->where("pd.id",$id)->find();
        $data['commission'] = getComm($level_id['level_id'],$data['pay_amount'],$data['pay_way'],$data['pay_status']);
        $data['pay_status'] = ($data['pay_status'] == 1)?"支付成功":"待支付";
        $data['status'] = ($data['status'] == 1)?"已结算":"未结算";
        $data['pay_way'] = getPayType($data['pay_way']);
        $data['pay_type'] = getPayClass($data['pay_type']);
        $this->assign("data",$data);
        return view("detail");
    }

    public function search(){
        $search = input("post.search");
        $deposit= db("promote_deposit")->field("id,promote_account,pay_order_number,create_time,pay_status")->where("promote_account",$search)->order("id desc")->limit(0,20)->select();
        foreach ($deposit as $key=>$value){
            $deposit[$key]['create_time'] = date("Y-m-d H:i:s",$value['create_time']);
            if($deposit[$key]['pay_status'] == 1){
                $deposit[$key]['pay_status'] = "支付成功";
            }else{
                $deposit[$key]['pay_status'] = "待支付";
            }
        }
        $this->assign("deposit",$deposit);
        return view("search");
    }
}