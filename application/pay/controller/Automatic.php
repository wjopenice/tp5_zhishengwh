<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/5/4
 * Time: 13:03
 */
namespace app\pay\controller;
use think\Controller;
use think\Db;
class Automatic extends Controller {
    public function sett(){
        $mytime = date("Y-m-d", strtotime("-1 day"));
        $endtime = strtotime($mytime." 23:59:59");
        //打开日志log
        $logFile = fopen(dirname(__FILE__)."/auto.txt", "a+");
        fwrite($logFile, "有访问\r\n\r\n");
        $time = strtotime(date('Y-m-d',time()) . "23:59:59");
        //扫码订单结算
        $scan_data = db('promote_deposit')
            ->field('promote_id,sum(pay_amount) pay_amount')
            ->where('pay_type != 2 and pay_status = 1 and status = 0 and pay_way in (1,2,3) and create_time <= ' . $endtime)
            ->group('promote_id')
            ->select();
        fwrite($logFile, db('promote_deposit')->getLastSql()."\r\n\r\n");
        $data = db('promote_deposit')
            ->field('id')
            ->where('pay_type != 2 and pay_status = 1 and status = 0 and pay_way in (1,2,3) and create_time <= ' . $endtime)
            ->select();
        fwrite($logFile, db('promote_deposit')->getLastSql()."\r\n\r\n");
        if(!empty($scan_data) && !empty($data)){
            fwrite($logFile, "有数据\r\n\r\n");
            Db::startTrans();
            try{
                foreach($data as $k=>$v){
                    $p_updata['status'] = 1;
                    $p_status = db('promote_deposit')->where('id',$v['id'])->update($p_updata);
                }

                foreach ($scan_data as $key=>$val) {
                    $a_status = db('promote')->where('id',$val['promote_id'])->setInc('money',$val['pay_amount']);
                    $ta_status = db('promote')->where('id',$val['promote_id'])->setInc('total_money',$val['pay_amount']);
                    $this->writeLog($val['promote_id'],$val['pay_amount'],'PC扫码');
                }
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
            }
        }else{
            fwrite($logFile, "无数据\r\n\r\n");
        }
    }

    //H5微信自动结算
    public function h5_wetch_sett(){
        $mytime = date("Y-m-d", strtotime("-1 day"));
        $endtime = strtotime($mytime." 23:59:59");
        //扫码订单结算
        $wetch_data = db('promote_deposit')
            ->field('promote_id,sum(pay_amount) pay_amount')
            ->where('pay_status = 1 and status = 0 and pay_way = 4 and create_time <= ' . $endtime)
            ->group('promote_id')
            ->select();
        $data = db('promote_deposit')
            ->field('id')
            ->where('pay_status = 1 and status = 0 and pay_way = 4 and create_time <= ' . $endtime)
            ->select();
        if(!empty($wetch_data) && !empty($data)){
            Db::startTrans();
            try{
                foreach($data as $k=>$v){
                    $p_updata['status'] = 1;
                    $p_status = db('promote_deposit')->where('id',$v['id'])->update($p_updata);
                }

                foreach ($wetch_data as $key=>$val) {
                    $a_status = db('promote')->where('id',$val['promote_id'])->setInc('h5_wetch_money',$val['pay_amount']);
                    $ta_status = db('promote')->where('id',$val['promote_id'])->setInc('wetch_money',$val['pay_amount']);
                    $this->writeLog($val['promote_id'],$val['pay_amount'],'H5微信');
                }
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
            }
        }
    }

    //H5支付宝自动结算
    public function h5_alipay_sett(){
        $mytime = date("Y-m-d", strtotime("-1 day"));
        $endtime = strtotime($mytime." 23:59:59");
        //订单结算
        $alipay_data = db('promote_deposit')
            ->field('promote_id,sum(pay_amount) pay_amount')
            ->where('pay_status = 1 and status = 0 and pay_way = 5 and create_time <= ' . $endtime)
            ->group('promote_id')
            ->select();
        $data = db('promote_deposit')
            ->field('id')
            ->where('pay_status = 1 and status = 0 and pay_way = 5 and create_time <= ' . $endtime)
            ->select();
        if(!empty($alipay_data) && !empty($data)){
            Db::startTrans();
            try{
                foreach($data as $k=>$v){
                    $p_updata['status'] = 1;
                    $p_status = db('promote_deposit')->where('id',$v['id'])->update($p_updata);
                }

                foreach ($alipay_data as $key=>$val) {
                    $a_status = db('promote')->where('id',$val['promote_id'])->setInc('h5_alipay_money',$val['pay_amount']);
                    $ta_status = db('promote')->where('id',$val['promote_id'])->setInc('alipay_money',$val['pay_amount']);
                    $this->writeLog($val['promote_id'],$val['pay_amount'],'H5支付宝');
                }
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
            }
        }
    }

    public function writeLog($id,$money,$type){
        // 结算日志
        $logcotent = '结算金额为 '. $money. '--'.$type . '--商户ID为： '.$id;
        $log_data = [
            'pid' => $id,
            'contet' => $logcotent,
            'money' => $money,
            'ip' => $this->request->ip(),
            'time' => time(),
        ];
        db('promote_accounts_log')->insert($log_data);
    }
}