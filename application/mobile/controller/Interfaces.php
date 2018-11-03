<?php
namespace app\mobile\controller;

use app\common\controller\Mobile;

class Interfaces extends Mobile{

    public function listx(){
        if(request()->isPost()){
            $id = input("post.id");
            $bool = db("pay_interface")->where('id',$id)->delete();
            if($bool){
                return json(["msg"=>"ok"]);
            }else{
                return json(["msg"=>"error"]);
            }
        }else{
            $pay = db("pay_interface")->field("id,pay_title,pay_number,pay_appid,pay_type")->select();
            foreach ($pay as $key=>$value){
                $pay[$key]['pay_type'] = getPayClass($value['pay_type']);
            }
            $this->assign("pay",$pay);
            return view("list");
        }
    }
    public function add(){
        if(request()->isPost()){
            $input = input("post.");
            $type = ["中宝",'通联', '环迅','千玺','爱加密','点缀','兴业','聚森','快接'];
            $input['pay_type'] = array_search($input['pay_type'],$type);
            $input['status'] = $input['status'] == '启用'?  1 : 2;
            $input['newest_time'] = time();
            $input['url'] = "";
            $bool = db("pay_interface")->insert($input);
            if($bool){
                $this->success("添加成功","/mobile/interfaces",2);
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
            $type = ["中宝",'通联', '环迅','千玺','爱加密','点缀','兴业','聚森','快接'];
            $input['pay_type'] = array_search($input['pay_type'],$type);
            $input['wetch_status'] = $input['wetch_status'] == "启用" ?  1 : 0;
            $input['alipay_status'] = $input['alipay_status'] == "启用" ?  1 : 0;
            $input['h5_alipay_status'] = $input['h5_alipay_status'] == "启用" ?  1 : 0;
            $input['h5_wetch_status'] = $input['h5_wetch_status'] == "启用" ?  1 : 0;
            $input['status'] = $input['status'] == "启用" ?  1 : 2;
            $input['end_time'] = time();
            $input['url'] = "";
            $bool = db("pay_interface")->where("id",$id)->update($input);
            if($bool){
                $this->success("修改成功","/mobile/interfaces",2);
            }else{
                $this->error("修改失败");
            }
        }else{
            $data = db("pay_interface")->where("id",$id)->find();
            $data['pay_type'] = getPayClass($data['pay_type']);
            $data['wetch_status'] = $data['wetch_status'] == 1 ?  "启用" : "禁止";
            $data['alipay_status'] = $data['alipay_status'] == 1 ?  "启用" : "禁止";
            $data['h5_alipay_status'] = $data['h5_alipay_status'] == 1 ?  "启用" : "禁止";
            $data['h5_wetch_status'] = $data['h5_wetch_status'] == 1 ?  "启用" : "禁止";
            $data['status'] = $data['status'] == 1 ?  "启用" : "禁止";
            $this->assign("data",$data);
            return view("detail");
        }
    }

}