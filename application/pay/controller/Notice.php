<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/25
 * Time: 15:34
 */
namespace app\pay\controller;
use app\common\controller\Pay;
class Notice extends Pay {

    public function index(){
        return view("index");
    }

    //获取公告数据
    public function getnotice(){
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
            $data  = db('hands_notice')->order('id asc')->select();
            $count = db('hands_notice')->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']      =  $v['id'];
                    $v['title']   =  $v['title'];
                    $v['content']   =  $v['content'];
                    $v['ctime']   =  $v['ctime'] ? date('Y-m-d H:i:s',$v['ctime']) : '';
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

    //添加公告
    public function add(){
        if(!request()->isPost()){
            return view("add");
        }else{
            $data = input('post.');
            $data['ctime'] = time();
            $re = db('hands_notice')->insertGetId($data);
            if($re){
                return [
                    'code' => 1,
                    'msg' => '添加成功'
                ];
            }else{
                return [
                    'code' => 0,
                    'msg' => '添加失败'
                ];
            }
        }
    }

    //编辑公告
    public function edit(){
        if(!request()->isPost()){
            $id = input('get.id');
            $find = db('hands_notice')->where("id",$id)->find();
            $this->assign('find',$find);
            return view("edit");
        }else{
            $data = input('post.');
            $data['ctime'] = time();
            $re = db('hands_notice')->where('id',$data['id'])->update($data);
            if($re){
                return [
                    'code' => 1,
                    'msg' => '添加成功'
                ];
            }else{
                return [
                    'code' => 0,
                    'msg' => '添加失败'
                ];
            }
        }
    }

    //删除公告
    public function del(){
        $id = input('post.id');
        $re = db('hands_notice')->where("id",$id)->delete();
        if($re){
            return [
                'code' => 1,
                'msg' => '删除成功'
            ];
        }else{
            return [
                'code' => 0,
                'msg' => '删除失败'
            ];
        }
    }

    public function uploadimages() {
        if (!request()->isPost()) {
            $this->error('上传失败');
        } else {
            $avatar = request()->file('file');
            if (!empty($avatar)) {
                $file_path = DATA_PATH . 'uploads/notice';
                if (!is_dir($file_path)) {
                    mkdir($file_path, 0777, true);
                }
                $info = $avatar->move($file_path);
                if ($info) {
                    $filesave = $info->getSaveName();
                    $row      = '/data/uploads/notice/' . str_replace('\\', "/", $filesave);
                    echo json_encode([
                            'code' => '0',
                            'msg' => '上传成功',
                            'data' => [
                                'src' => $row,
                                'title'=>''
                            ]
                        ]
                    );
                } else {
                    $err = $avatar->getError();
                    echo json_encode(['msg' => '上传失败']);;
                }
            }
        }
    }
}