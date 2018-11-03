<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/3/21
 * Time: 17:35
 */

namespace app\pay\controller;
use app\common\controller\Pay;
use think\Config;
class Stall extends Pay {

    public function index(){
        return view("index");
    }

    //获取所有档位
    public function getstall(){
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

            $where   = "bb.promote_id = " . get_pro_id() . " and bb.status = 1";

            //---------------END--------------//
            $data  = db('promote_level')->order('ident asc')->select();
            $count = db('promote_level')->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id']      =  $v['id'];
                    $v['level']   =  $v['level'];
                    $v['h5_wetch_revenue']   =  $v['h5_wetch_revenue'];
                    $v['h5_alipay_revenue']   =  $v['h5_alipay_revenue'];
                    $v['comm_revenue']   =  $v['comm_revenue'];
                    $v['with_cycle']   =  $v['with_cycle'];
                    $v['revenue'] = (float)$v['revenue'];
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

    //档位设置
    public function editstall(){
        if(request()->isPost()){
            $editData = input('post.');
            if($editData){
                $map = [
                    'id' => $editData['id'],
                ];
                $up_data = [
                    'level'    => $editData['level'],
                    'revenue'  => $editData['revenue'],
                    'h5_wetch_revenue'  => $editData['h5_wetch_revenue'],
                    'h5_alipay_revenue'  => $editData['h5_alipay_revenue'],
                    'comm_revenue'  => $editData['comm_revenue'],
                    'with_cycle'  => $editData['with_cycle'],
                    'ident'    => $editData['ident'],
                ];
                $result = db('promote_level')->where($map)->update($up_data);
                if($result){
                    rwLog([
                        'content' => "修改档位".$editData['level']."的提现费率为".$editData['revenue'],
                        'login_ip' => get_server_ip(),
                        'time'     => time(),
                        'pid'      => get_pro_id(),
                        'identity' => 2
                    ]);
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
            }else{
                return [
                    'code' => 0,
                    'msg'  => '数据异常'
                ];
            }
        }else{
            $id = input('get.id');
            $result = db('promote_level')->where("id=".$id)->find();
            $this->assign('data',$result);
            return view("editstall");
        }
    }

    //新增档位
    public function add()
    {
        if(!request()->isPost()){
            return view("add");
        }else{
            $data = input('post.');
            if($data){
                $find = db('promote_level')->where("level='".$data['level']."'")->find();
                if($find){
                    return [
                        'code' => 0,
                        'msg'  => '档位已存在'
                    ];
                }else{
                    $ins_data = [
                        'level' => $data['level'],
                        'revenue' => $data['revenue'],
                        'h5_revenue' => $data['h5_revenue'],
                        'ident'    => $data['ident'],
                    ];
                    $re = db('promote_level')->insert($ins_data);
                    if($re){
                        rwLog(array(
                            'content' => "新增档位".$data['level']."的提现费率为".$data['revenue'],
                            'login_ip' => get_server_ip(),
                            'time'   => time(),
                            'pid'   => get_pro_id(),
                            'identity' => 2
                        ));
                        return [
                            'code' => 1,
                            'msg'  => '新增成功'
                        ];
                    }else{
                        return [
                            'code' => 0,
                            'msg'  => '新增失败'
                        ];
                    }
                }
            }else{
                return [
                    'code' => 0,
                    'msg'  => '参数为空'
                ];
            }
        }
    }

    //档位删除
    public function delstall(){
        if(!request()->isPost()){
            return [
                'code' => 0,
                'msg'  => '数据异常'
            ];
        }else{
            $id = input('post.id');
            if($id){
                $result = db('promote_level')->where("id=".$id)->delete();
                if($result){
                    rwLog([
                        'content' => "删除档位",
                        'login_ip' => get_server_ip(),
                        'time'     => time(),
                        'pid'      => get_pro_id()
                    ]);
                    return [
                        'code' => 1,
                        'msg'  => '删除成功'
                    ];
                }else{
                    return [
                        'code' => 0,
                        'msg'  => '删除失败'
                    ];
                }
            }
        }

    }
}