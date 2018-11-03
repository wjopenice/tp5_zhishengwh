<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/5/29
 * Time: 17:50
 */

namespace app\pay\controller;
use app\common\controller\Pay;
use think\Config;
use lib\Excel;
class Supplement extends Pay
{
    public function index(){
        return view("index");
    }

    //获取所有充值列表
    public function getData()
    {
        if (!request()->isGet()) {
            return [
                'code' => 200,
                'msg' => '数据请求异常'
            ];
        } else {

            $page = input('get.page');
            $limit = input('get.limit');
            $sPage = ($page - 1) * $limit;
            //TODO::待加入条件查询
            $sta = strtotime(input('get.start_time'));
            $end = strtotime(input('get.first_time'));
            $order_number = input('get.order_number');
            $pay_order_number = input('get.pay_order_number');
            $account = input('get.account');
            $pay_way = input('get.pay_way');
            $pay_status = input('get.pay_status');
            $pay_type = input('get.pay_type');
            $where = 1;

            if (!empty($sta) && !empty($end)) {
                $where .= ' and (pd.create_time > ' . $sta . ' and pd.create_time <' . $end . ")";
            } else {
                if (!empty($sta) && empty($end)) {
                    $where .= ' and pd.create_time > ' . $sta;
                }
                if (empty($sta) && !empty($end)) {
                    $where .= ' and pd.create_time <' . $end;
                }
            }
            if (!empty($order_number)) {
                $where .= " and pd.order_number='" . $order_number . "'";
            }
            if (!empty($account)) {
                $where .= " and pd.promote_account='" . $account . "'";
            }
            if (!empty($pay_order_number)) {
                $where .= " and pd.pay_order_number='" . $pay_order_number . "'";
            }
            if (isset($pay_status) && $pay_status != '') {
                $where .= " and pd.pay_status = " . $pay_status;
            }
            if ($pay_way) {
                $where .= " and pd.pay_way=" . $pay_way;
            }
            if (input('status') != null) {
                $where .= " and pd.status=" . input('status');
            }
            if ($pay_type) {
                $where .= " and pd.pay_type=" . $pay_type;
            }
            //---------------END--------------//
            $data = db('hands_supp_order')
                ->alias('h')
                ->join('promote_deposit p','p.id=h.order_id')
                ->where($where)
                ->order('h.id desc')
                ->limit($sPage, $limit)
                ->select();
            $count = db('hands_supp_order')
                ->alias('h')
                ->join('promote_deposit p','p.id=h.order_id')
                ->where($where)
                ->count();
            if (!empty($data)) {
                foreach ($data as $k => &$v) {
                    $v['id'] = $v['id'];
                    $v['order_number'] = $v['order_number'];
                    $v['pay_amount'] = $v['pay_amount'];
                    $v['account'] = $v['account'];
                    $v['select_time'] = $v['select_time'] ? date('Y-m-d H:i:s', $v['select_time']) : '';
                    $v['create_time'] = $v['create_time'] ? date('Y-m-d H:i:s', $v['create_time']) : '';
                    $v['key'] = $k + 1;
                    $v['type'] = $v['type'] ? '商户' : '管理员';
                }
                return [
                    'code' => 0,
                    'msg' => '',
                    'count' => $count,
                    'data' => $data
                ];
            } else {
                return [
                    'code' => 1,
                    'msg' => '没有数据'
                ];
            }
        }
    }
}