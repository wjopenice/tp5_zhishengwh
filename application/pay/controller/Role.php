<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/4
 * Time: 15:08
 */
namespace app\pay\controller;
use app\common\controller\Pay;
use think\Config;
use lib\Excel;
class Role extends Pay {

    //角色列表页
    public function index(){
        return view("index");
    }

    //获取所有角色数据
    public function getrole(){
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
            $where   = "status != 0 and is_dis = 1";
            $data  = db('hands_role')
                ->where($where)
                ->order('rid desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('hands_role')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id'] =  $v['rid'];
                    $v['role_name'] =  $v['role_name'];
                    $v['status'] = $v['status'] ? '正常' : '禁用';
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

    public function is_zai($array,$idArr){
        foreach($array as $key=>$val) {
            if(in_array($array[$key]['id'],$idArr)){
                $array[$key]['selected'] = 1;
            }else{
                $array[$key]['selected'] = 0;
            }
        }
        return $array;
    }

    //权限分配
    public function roledistr(){
        if(!request()->isPost()){
            $id = input('get.id');
            //查询当前管理员权限
            $find = db('hands_role_config')->where('rid',$id)->find();
            $all = getALLRole();//查询所有权限
            if($find){
                if($find['top_id']){
                    $find['top_id'] = explode(',',$find['top_id']);
                    foreach($all as $k=>$v){
                        if(in_array($all[$k]['id'],$find['top_id'])){
                            $all[$k]['selected'] = 1;
                        }else{
                            $all[$k]['selected'] = 0;
                        }
                    }
                }else{
                    foreach($all as $k=>$v){
                        $all[$k]['selected'] = 0;
                    }
                }
                if($find['f_menu_id']){
                    $find['f_menu_id'] = explode(',',$find['f_menu_id']);
                    foreach($all as $k=>$v){
                        foreach($all[$k]['second'] as $key=>$val) {
                            if (in_array($all[$k]['second'][$key]['id'], $find['f_menu_id'])) {
                                $all[$k]['second'][$key]['selected'] = 1;
                            } else {
                                $all[$k]['second'][$key]['selected'] = 0;
                            }
                        }
                    }
                }else{
                    foreach($all as $k=>$v){
                        foreach($all[$k]['second'] as $key=>$val) {
                                $all[$k]['second'][$key]['selected'] = 0;
                        }
                    }
                }
            }else{
                foreach($all as $k=>$v){
                    $all[$k]['selected'] = 0;
                    foreach($all[$k]['second'] as $key=>$val) {
                        $all[$k]['second'][$key]['selected'] = 0;
                    }
                }
            }

            $this->assign('id',$id);
            $this->assign('role',$all);
            $this->assign('find',$find);
            return view("roledistr");
        }else{
            $data = input('post.');
            if(!isset($data['class_id']) && !isset($data['rid'])){
                return [
                    'code' => 0,
                    'msg'  => '权限分配错误'
                ];
                exit;
            }
            $change_data = [
                'top_id' => implode(",", $data['class_id']),
                'f_menu_id' => implode(",", $data['rid']),
            ];
            $find = db('hands_role_config')->where('rid',$data['id'])->find();
            $is_dis = db('hands_role')->where('rid',$data['id'])->value('is_dis');
            if($is_dis == 2){
                return [
                    'code' => 0,
                    'msg'  => '该角色不需要分配权限'
                ];
                exit;
            }
            if($find){
                $result = db('hands_role_config')->where('rid',$data['id'])->update($change_data);
            }else{
                $ins_data = [
                    'rid'  =>  $data['id'],
                    'top_id' => implode(",", $data['class_id']),
                    'f_menu_id' => implode(",", $data['rid']),
                ];
                $result = db('hands_role_config')->insertGetId($ins_data);
            }
            if($result){
                return [
                    'code' => 1,
                    'msg'  => '分配成功'
                ];
            }else{
                return [
                    'code' => 0,
                    'msg'  => '分配失败'
                ];
            }
        }
    }

    //角色添加
    public function add(){
        if(!request()->isPost()){
            return view("add");
        }else{
            $data = input('post.');
            $find = db('hands_role')->where("role_name",$data['title'])->find();
            if($find){
                return [
                    'code' => 0,
                    'msg'  => '角色名称已存在'
                ];
            }else{
                $ins_data = [
                    'role_name' => $data['title'],
                    'status' => $data['status']
                ];
                $result = db('hands_role')->insertGetId($ins_data);
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

    //角色编辑
    public function edit(){
        if(!request()->isPost()){
            $id = input('get.id');
            $find = db('hands_role')->where("rid",$id)->find();
            $this->assign('find',$find);
            return view("edit");
        }else{
            $data = input('post.');
            $find = db('hands_role')->where("rid != " . $data['rid'] . " and role_name = '" . $data['role_name'] . "'")->find();
            if($find){
                return [
                    'code' => 0,
                    'msg'  => '角色名称已存在'
                ];
            }else{
                $up_data = [
                    'role_name' => $data['title'],
                    'status' => $data['status']
                ];
                $result = db('hands_role')->where("rid",$data['rid'])->update($up_data);
                if($result){
                    return [
                        'code' => 1,
                        'msg'  => '编辑成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '编辑失败'
                    ];
                }
            }
        }
    }
}