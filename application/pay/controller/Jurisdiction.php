<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/2
 * Time: 10:53
 */
namespace app\pay\controller;
use app\common\controller\Pay;
use think\Config;
use lib\Excel;
class Jurisdiction extends Pay {

    public function index(){
        return view("index");
    }

    //获取权限列数据
    public function getjuris(){
        if(!request()->isGet()){
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        }else{
            $page  = input('get.page');
            $limit = input('get.limit');
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询
            $where   = "praendid != 0";
            $data  = db('hands_menu')
                ->where($where)
                ->order('id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('hands_menu')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['title'] =  $v['title'];
                    $v['praendid'] = db('hands_menu')->where('id',$v['praendid'])->value('title');
                    $v['hide'] = $v['hide'] ? '隐藏' : '显示';
                    $v['status'] = $v['status'] ? '禁用' : '正常';
                    $v['quick'] = $v['quick'] ? '否' : '是';
                    $v['url'] = $v['url'];
                    $v['key']           = $k + 1;
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

    //权限分类
    public function type(){
        return view("type");
    }

    //获取权限分类数据
    public function gettype(){
        if(!request()->isGet()){
            return [
                'code' => 200,
                'msg'  => '数据请求异常'
            ];
        }else{
            $page  = input('get.page');
            $limit = input('get.limit');
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询
            $where   = "praendid = 0";
            $data  = db('hands_menu')
                ->where($where)
                ->order('id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('hands_menu')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['title'] =  $v['title'];
                    $v['faicon'] =  $v['faicon'];
                    $v['faicon2'] =  $v['faicon2'];
                    $v['hide'] = $v['hide'] ? '隐藏' : '显示';
                    $v['status'] = $v['status'] ? '禁用' : '正常';
                    $v['solf'] =  $v['solf'];
                    $v['key']           = $k + 1;
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

    //添加权限分类
    public function addclass(){
        if(!request()->isPost()){
            return view("addclass");
        }else{
            $data = input('post.');
            $find = db('hands_menu')->where("title='".$data['title']."'")->find();
            if($find){
                return [
                    'code' => 0,
                    'msg'  => '分类已存在'
                ];
            }else{
                $result = db('hands_menu')->insertGetId($data);
                if($result){
                    return [
                        'code' => 1,
                        'msg'  => '添加成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '添加失败'
                    ];
                }
            }
        }
    }

    //添加权限
    public function add(){
        if(!request()->isPost()){
            //查询所有权限分类
            $data  = db('hands_menu')->where("praendid=0")->order('id asc')->select();
            $this->assign('data',$data ?  : '');
            return view("add");
        }else{
            $data = input('post.');
            $find = db('hands_menu')->where("title='".$data['title']."' or url='".$data['url']."'")->find();
            if($find){
                return [
                    'code' => 0,
                    'msg'  => '权限或路由已存在'
                ];
            }else{
                $result = db('hands_menu')->insertGetId($data);
                if($result){
                    return [
                        'code' => 1,
                        'msg'  => '添加成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '添加失败'
                    ];
                }
            }
        }
    }

    //权限修改
    public function edit(){
        if(!request()->isPost()){
            $id = input('get.id');
            //查询所有权限分类
            $data  = db('hands_menu')->where("praendid=0")->order('id asc')->select();
            //查询当前权限数据
            $find = db('hands_menu')->where('id',$id)->find();

            $this->assign('data',$data ?  : '');
            $this->assign('find',$find);
            return view("edit");
        }else{
            $data = input('post.');
            $find = db('hands_menu')->where("(title='".$data['title']."' or url='".$data['url']."') and id != ".$data['id'])->find();
            if($find){
                return [
                    'code' => 0,
                    'msg'  => '权限或路由已存在'
                ];
            }else{
                $up_data = [
                    'title' => $data['title'],
                    'praendid' => $data['praendid'],
                    'hide' => $data['hide'],
                    'solf' => $data['solf'],
                    'status' => $data['status'],
                    'url' => $data['url'],
                    'quick' => $data['quick'],
                ];
                $result = db('hands_menu')->where("id",$data['id'])->update($up_data);
                if($result){
                    return [
                        'code' => 1,
                        'msg'  => '修改成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '修改失败'
                    ];
                }
            }
        }
    }

    //权限分类修改
    public function editclass(){
        if(!request()->isPost()){
            $id = input('get.id');
            //查询当前权限数据
            $find = db('hands_menu')->where('id',$id)->find();
            $this->assign('find',$find);
            return view("editclass");
        }else{
            $data = input('post.');
            $find = db('hands_menu')->where("title='".$data['title']."' and id != ".$data['id'])->find();
            if($find){
                return [
                    'code' => 0,
                    'msg'  => '权限分类已存在'
                ];
            }else{
                $up_data = [
                    'title' => $data['title'],
                    'faicon' => $data['faicon'],
                    'faicon2' => $data['faicon2'],
                    'hide' => $data['hide'],
                    'solf' => $data['solf'],
                    'status' => $data['status'],
                ];
                $result = db('hands_menu')->where("id",$data['id'])->update($up_data);
                if($result){
                    return [
                        'code' => 1,
                        'msg'  => '修改成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '修改失败'
                    ];
                }
            }
        }
    }

    //权限删除
    public function del(){
        $id = input('post.id');
        $praendid = db('hands_menu')->where("id",$id)->find();
        if($praendid['praendid'] == 0){
            $data = db('hands_menu')->where('praendid',$id)->select();
            if($data){
                return [
                    'code' => 0,
                    'msg'  => '删除失败，请先删除该权限分类下的子权限'
                ];
                exit;
            }else{
                $result = db('hands_menu')->where('id',$id)->delete();
            }
        }else{
            $result = db('hands_menu')->where('id',$id)->delete();
        }
        if($result){
            return [
                'code' => 1,
                'msg'  => '删除成功'
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