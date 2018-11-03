<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/3/28
 * Time: 10:38
 */
namespace app\pay\controller;
use think\Config;
use lib\Excel;
class Pay extends \app\common\controller\Pay {
    //接口列表
    public function index(){
        return view("index");
    }

    //获取所有接口信息
    public function getine(){
        if (!request()->isGet()) {
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        } else {

            $page  = input('get.page');
            $limit = input('get.limit');
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询

            //---------------END--------------//
            $data  = db('pay_interface')->order('id desc')->limit($sPage, $limit)->select();
            $count = db('pay_interface')->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['pay_title'] =  $v['pay_title'];
                    $v['pay_number'] = $v['pay_number'];
                    $v['pay_appid'] = $v['pay_appid'];
                    $v['pay_cusid'] = $v['pay_cusid'];
                    $v['pay_appkey'] = $v['pay_appkey'];
                    $v['pay_type'] = getPayClass($v['pay_type']);
                    $v['wetch_status'] = $v['wetch_status'];
                    $v['alipay_status'] = $v['alipay_status'];
                    $v['h5_wetch_status'] = $v['h5_wetch_status'];
                    $v['h5_alipay_status'] = $v['h5_alipay_status'];
                    $v['url'] = $v['url'];
                    $v['key']   = $k + 1;
                    $v['status'] = $v['status'];
                }
                return [
                    'code'  => 0,
                    'msg'   => '',
                    'count' => $count,
                    'data'  => $data
                ];
            } else {
                return [
                    'code' => 1,
                    'msg'  => '没有数据'
                ];
            }
        }
    }

    //修改支付接口状态
    public function upstatus(){
        $id  = input('get.id');
        $status = db('pay_interface')->where("id=".$id)->value("status");
        if($status == 1){
            $up_data = [
                'status' => 2,
                'end_time' => time()
            ];
        }else{
            $up_data = [
                'status' => 1,
                'newest_time' => time()
            ];
        }
        db('pay_interface')->where("id=".$id)->update($up_data);
        return [
            'code' => 1,
            'status' => $up_data['status']
        ];
    }

    //修改支付接口中,微信的支付状态
    public function upwetchstatus(){
        $id  = input('get.id');
        $status = db('pay_interface')->where("id=".$id)->value("wetch_status");
        if($status == 1){
            $up_data = [
                'wetch_status' => 0,
                'end_time' => time()
            ];
        }else{

            $up_data = [
                'wetch_status' => 1,
                'newest_time' => time()
            ];
        }
        db('pay_interface')->where("id=".$id)->update($up_data);
        return [
            'code' => 1,
            'wetch_status' => $up_data['wetch_status']
        ];
    }

    //修改支付接口中,H5微信的支付状态
    public function uph5wetchstatus(){
        $id  = input('get.id');
        $status = db('pay_interface')->where("id=".$id)->value("h5_wetch_status");
        if($status == 1){
            $up_data = [
                'h5_wetch_status' => 0,
                'end_time' => time()
            ];
        }else{

            $up_data = [
                'h5_wetch_status' => 1,
                'newest_time' => time()
            ];
        }
        db('pay_interface')->where("id=".$id)->update($up_data);
        return [
            'code' => 1,
            'h5_wetch_status' => $up_data['h5_wetch_status']
        ];
    }

    //修改支付接口中,支付宝的支付状态
    public function upalipaystatus(){
        $id  = input('get.id');
        $status = db('pay_interface')->where("id=".$id)->value("alipay_status");
        if($status == 1){
            $up_data = [
                'alipay_status' => 0,
                'end_time' => time()
            ];
        }else{

            $up_data = [
                'alipay_status' => 1,
                'newest_time' => time()
            ];
        }
        db('pay_interface')->where("id=".$id)->update($up_data);
        return [
            'code' => 1,
            'alipay_status' => $up_data['alipay_status']
        ];
    }

    //修改支付接口中,H5支付宝的支付状态
    public function uph5alipaystatus(){
        $id  = input('get.id');
        $status = db('pay_interface')->where("id=".$id)->value("h5_alipay_status");
        if($status == 1){
            $up_data = [
                'h5_alipay_status' => 0,
                'end_time' => time()
            ];
        }else{

            $up_data = [
                'h5_alipay_status' => 1,
                'newest_time' => time()
            ];
        }
        db('pay_interface')->where("id=".$id)->update($up_data);
        return [
            'code' => 1,
            'h5_alipay_status' => $up_data['h5_alipay_status']
        ];
    }

    //添加商户
    public function add(){
        if(!request()->isPost()){
            return view("add");
        }else{
            $data = input('post.');
                $ins_data = [
                    'pay_type' => $data['pay_type'],
                    'pay_title' => $data['pay_title'],
                    'pay_number' => $data['pay_number'],
                    'pay_appid' => $data['pay_appid'],
                    'pay_cusid' => $data['pay_cusid'],
                    'pay_appkey' => $data['pay_appkey'],
                    'status' => $data['status'],
                    'newest_time' => time()
                ];
                $result = db('pay_interface')->insertGetId($ins_data);
                if ($result) {
                    return [
                        'code' => 1,
                        'msg' => '添加成功'
                    ];
                } else {
                    return [
                        'code' => 0,
                        'msg' => '添加失败'
                    ];
                }
        }

    }

    //修改商户资料
    public function edit(){
        if(!request()->isPost()){
            $id = input('get.id');
            $find = db('pay_interface')->where("id=".$id)->find();
            $this->assign('data',$find);
            return view("edit");
        }else{
            $data = input('post.');
            if($data){
                $up_data = [
                    'pay_type' => $data['pay_type'],
                    'pay_title' => $data['pay_title'],
                    'pay_number' => $data['pay_number'],
                    'pay_appid' => $data['pay_appid'],
                    'pay_cusid' => $data['pay_cusid'],
                    'pay_appkey' => $data['pay_appkey'],
                    'status' => $data['status'],
                    'newest_time' => time()
                ];
                $result = db('pay_interface')->where("id",$data['id'])->update($up_data);
                if ($result) {
                    return [
                        'code' => 1,
                        'msg' => '编辑成功'
                    ];
                } else {
                    return [
                        'code' => 0,
                        'msg' => '编辑失败'
                    ];
                }


            }else{
                return [
                    'code' => 0,
                    'msg'  => '数据错误'
                ];
            }
        }
    }

    //删除商户
    public function del(){
        $id = input('post.id');
        $resutl = db('pay_interface')->where("id",$id)->delete();
        if($resutl){
            return [
                'code' => 1,
                'msg' => '删除成功'
            ];
            exit;
        }else{
            return [
                'code' => 0,
                'msg'  => '删除失败'
            ];
            exit;
        }
    }
}