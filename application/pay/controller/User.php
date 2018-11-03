<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/4/19
 * Time: 9:52
 */
namespace app\pay\controller;
use app\common\controller\Pay;
class User extends Pay {
    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';
    //重置密码
    public function savepass(){
        if(!request()->isPost()){
            return view("savepass");
        }else{
            $id = get_pro_id();
            $data = input('post.');
            $admin_pass = db('hands_admin')->where("id=".$id)->value('pass');
            if($this->think_ucenter_md5($data['old_pass'],self::DATA_AUTH_KEY) != $admin_pass){
                return [
                    'code' => 0,
                    'msg'  => '原密码不正确'
                ];
            }

            if($this->think_ucenter_md5($data['new_pass'],self::DATA_AUTH_KEY) == $admin_pass){
                return [
                    'code' => 0,
                    'msg'  => '新密码不能与原密码一样'
                ];
            }

            $save = db('hands_admin')->where("id=".$id)->update([
                'pass' => $this->think_ucenter_md5($data['new_pass'],self::DATA_AUTH_KEY)
            ]);

            if($save){
                session(null);
                return [
                    'code' => 1,
                    'msg'  => '重置成功'
                ];
            }else{
                return [
                    'code' => 0,
                    'msg'  => '重置失败'
                ];
            }
        }
    }

    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    //管理员列表
    public function index(){
        return $this->fetch();
    }

    //获取管理员数据
    public function getuser(){
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
            $where   = 1;
            $data  = db('hands_admin')
                ->where($where)
                ->order('id desc')
                ->limit($sPage, $limit)
                ->select();

            $count = db('hands_admin')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id'] =  $v['id'];
                    $v['account'] =  $v['account'];
                    $v['grade'] =  $this->getRoleName($v['grade'],$v['roleId']);
                    $v['status'] = $v['status'] ? '正常' : '禁用';
                    $v['create_time'] = date('Y-m-d H:i:s');
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

    //获取管理员角色名称
    public function getrolename($grade,$rid){
        if($grade == 1){
            return '超级管理员';
        }else{
            $name = db('hands_role')->where("rid",$rid)->value('role_name');
            return $name;
        }
    }

    public function add(){
        if(!request()->isPost()){

            $role = db('hands_role')->select();

            $this->assign('role',$role);
            return view("add");
        }else{
            $data = input('post.');

            $in_data = [
                'account' => $data['account'],
                'pass' => think_ucenter_md5('shoushang2018', self::DATA_AUTH_KEY),
                'roleId' => $data['roleId'],
                'status' => $data['status'],
            ];
            $re = db('hands_admin')->insertGetId($in_data);
            if($re){
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

    //管理员信息修改
    public function edit(){
        if(!request()->isPost()){
            $id = input('get.id');
            $find = db('hands_admin')->where("id",$id)->find();

            $role = db('hands_role')->select();

            $this->assign('find',$find);
            $this->assign('role',$role);
            return view("edit");
        }else{
            $data = input('post.');

            $up_data = [
                'roleId' => $data['roleId'],
                'status' => $data['status'],
            ];
            $re = db('hands_admin')->where("id",$data['id'])->update($up_data);
            if($re){
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

    //删除管理员
    public function del(){
        $id = input('post.id');
        $re = db('hands_admin')->where("id",$id)->delete();
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
}