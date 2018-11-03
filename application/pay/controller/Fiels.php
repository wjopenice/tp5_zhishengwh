<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/5/24
 * Time: 10:28
 */
namespace app\pay\controller;

use app\common\controller\Pay;

class Fiels extends Pay {
    public function index(){
        return view("index");
    }

    //获取所有商户数据
    public function getfiels(){
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
//            $account = input('get.account');
//            $nickname = input('get.nickname');
            $where = 1;

            //---------------END--------------//
            $data  = db('hands_fiels')
                ->where($where)
                ->order('id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('hands_fiels')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']           = $v['id'];
                    $v['name']     = $v['name'];
                    $v['url']        = $v['url'];
                    $v['ctime']  = $v['ctime'] ? date('Y-m-d H:i:s', $v['ctime']) : '';
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

    public function selectfiels(){
        $id = input('post.id');
        $fiels = db('hands_fiels')->where('id',$id)->find();
        if($fiels){
            return [
                'code' => 1,
                'url'  => $fiels['url']
            ];
        }else{
            return [
                'code' => 0,
            ];
        }
    }
}