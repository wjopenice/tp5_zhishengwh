<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/6/6
 * Time: 14:35
 */
namespace app\pay\controller;

use app\common\controller\Pay;

class Black extends Pay {

    public function index(){
        return view("index");
    }

    //获取所有数据
    public function getBlackIp(){
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
            $where = 1;
            $ip = input('get.ip');
            if($ip != ''){
                $where .= " and ip=".$ip;
            }

            //---------------END--------------//
            $data  = db('hands_blacklist')
                ->where($where)
                ->order('id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('hands_blacklist')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']           = $v['id'];
                    $v['ip']     = $v['ip'];
                    $v['status']     = $v['status'] ? '解冻' : '冻结';
                    $v['create_time']  = $v['create_time'] ? date('Y-m-d H:i:s', $v['create_time']) : '';
                    $v['key']          = $k + 1;
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

    //添加黑名单
    public function add(){
        if(!request()->isPost()){
            return view("add");
        }else{
            $data = input('post.');
            $ins_data = [
                'ip' => $data['ip'],
                'create_time' => time()
            ];
            $result = db('hands_blacklist')->insertGetId($ins_data);
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

    //删除黑名单
    public function del(){
        $id = input('post.id');
        $result = db('hands_blacklist')->where('id',$id)->delete();
        if ($result) {
            return [
                'code' => 1,
                'msg' => '删除成功'
            ];
        } else {
            return [
                'code' => 0,
                'msg' => '删除失败'
            ];
        }
    }

    //IP充值记录查询
    public function select(){
        $id = input('get.id');
        $ip = db('hands_blacklist')->where('id',$id)->value('ip');
        $data = [
            'all' => db('promote_deposit')->where("pay_ip='".$ip."'")->count(),
            'success' => db('promote_deposit')->where("pay_ip='".$ip."'"." and pay_status=1")->count(),
            'error' => db('promote_deposit')->where("pay_ip='".$ip."'"." and pay_status=0")->count(),
        ];
        $this->assign('data',$data);
        return view("select");
    }

    //IP解冻
    public function filter(){
        $list = db('hands_blacklist')->select();
        foreach($list as $key=>$val){
            $data = [
                'all' => db('promote_deposit')->where("pay_ip='".$val['ip']."'")->count(),
                'success' => db('promote_deposit')->where("pay_ip='".$val['ip']."'"." and pay_status=1")->count(),
                'error' => db('promote_deposit')->where("pay_ip='".$val['ip']."'"." and pay_status=0")->count(),
            ];
            if($data['success'] > 0){
                if(time() - $val['create_time'] > 180 && $val['status'] == 0){
                    db('hands_blacklist')->where('id',$val['id'])->update(['status' => 1]);
                }
            }

        }

    }


}