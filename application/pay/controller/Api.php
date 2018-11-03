<?php
/**
 * Created by PhpStorm.
 * User: adminn
 * Date: 2018/5/8
 * Time: 17:41
 */
namespace app\pay\controller;
use think\Config;
use think\Controller;

class Api extends Controller{

    //环讯参数定义
    const Version = "v1.0.0";
    //商户号
    const MerCode = "207575";//"207973";"207575";
    //商户名
    const MerName = "广州手上科技有限公司";//"广州堡庆网络科技有限公司" 广州手上科技有限公司;

    const Account = "2075750014";//"2079730012";2075750014
    //const Key = "X12DYVzevDOVmFpYopdMQVwdG2pKg8MtycWHxTvZ5gNNlJe8AAeTP4X4t1NdZuOOrlDRDWHvy6FfH5cAcUF5dH15BESxdLRmQ8KQlNJN2TU4Oyvz8qXpds3hymjjJMSp";//堡庆
    const Key = "ykorm6Gh3UTcAeJZoBYUO2UApGYF25FHJRey42G6JY8kVwrY6LLKX20a0fsYC91YSWzG16e6OE3mNqXbSWAQa7Mykvz4kM38fPLL6u6w643LFw6Kd0zc2avua9HRhfEt";//手上
    const ApiUrl = "https://newpay.ips.com.cn/psfp-entry/services/order?wsdl";

    public function select(){
        $id = input('post.id');
        $find = db('promote_deposit')->where('id',$id)->find();
        switch ($find['pay_type']) {
            case 1:
                $this->beginPaySelect($find);
                break;
            case 2:
                echo $this->ipsPaySelect($find);
                break;
            case 3:
                echo json_encode(["code"=>0,"msg"=>"该订单不支持结果查询"]);
                //$this->beginPPPaySelect($find);
                exit;
                break;
            case 8:
                $this->KjPaySelect($find);
                break;
        }
    }

    //通联订单查询
    public function beginPaySelect($data){
        #支付配置
        $config["cusid"] = "55059304816K9C0";
        $config["appid"] = "00018876";
        $config["version"] = "11";
        $config["randomstr"] = $this->getRandom();//随机字符串
        $config['reqsn'] = $data['pay_order_number'];
        $config["sign"] = $this->SignArray($config,"2018Z0303B");//签名
        $paramsStr = $this->ToUrlParams($config);
        $url = "https://vsp.allinpay.com/apiweb/unitorder/query";

        $rsp = $this->request($url, $paramsStr);
        $rspArray = json_decode($rsp, true);
        if($this->validSign($rspArray)){
            $find = db('promote_deposit')->where("pay_order_number = '".$rspArray['reqsn']."'")->find();
            if($find){
                if($find['pay_order_number'] != $rspArray['reqsn']){
                    echo json_encode(["code"=>0,"msg"=>"订单号不匹配"]);
                    exit;
                }
                if($find['pay_status'] == 0 || $find['pay_status'] == 2 || $find['pay_status'] == 3){
                    //var_dump($rspArray);
                    if($rspArray['trxstatus'] == '0000'){
                        $pay_status = 1;
                        $msg = '支付成功';
                        $re = db('promote_deposit')->where("pay_order_number = '".$rspArray['reqsn']."'")->update(['pay_status' => $pay_status]);
                        if($re){
                            $this->suppOrder($find,session('account')['id']);
                        }
                    }else{
                        $msg = '待支付';
                        //$re = db('promote_deposit')->where("pay_order_number = '".$rspArray['reqsn']."'")->update(['pay_status' => 0]);
                        $re = 1;
                    }
                }else{
                    $msg = '支付成功';
                    $re = 1;
                }
                if($re){
                    $data = [
                        'pay_status' => $msg
                    ];
                    echo json_encode(["code"=>1,"msg"=>"查询订单状态成功--".$msg,'data' => $data]);
                    exit;
                }

                //var_dump($rspArray);
            }else{
                echo json_encode(["code"=>0,"msg"=>"订单不存在"]);
                exit;
            }
        }else{
            echo json_encode(["code"=>0,"msg"=>"验签失败"]);
            exit;
        }
    }

    //皮皮订单查询
    public function beginPPPaySelect($data){
        $pay_data = db('pay_interface')->where("pay_type",$data['pay_type'])->find();
        $APPID = $pay_data['pay_appid'];//'213779';
        $key = $pay_data['pay_appkey'];//'38BrIq5MXC0qYyUJhAI0X7D8lq3oKZvI';
        #支付配置
        $config = array(
            'OrderId' => $data['pay_order_number'],
            'ForUserId' => $APPID,
        );
        $url = "http://pay.1688dkj.com/API/Bank/";
        $config["Sign"] = iconv('UTF-8','GB2312',$this->SignPPArray($config,$key));//签名
        $rsp = $this->send_post($url, $config);
        $rspArray = iconv('GB2312','utf-8',$rsp);
        $rspArray = json_decode($rspArray, true);
        if($rspArray['errcode'] == '0'){

            if($data['pay_status'] == 0){
                //var_dump($rspArray);
                if($rspArray['Status'] == '0'){
                    $pay_status = 1;
                    $msg = '支付成功';
                    $re = db('promote_deposit')->where("pay_order_number = '".$data['pay_order_number']."'")->update(['pay_status' => $pay_status]);
                }else{
                    $msg = '待支付';
                    //$re = db('promote_deposit')->where("pay_order_number = '".$rspArray['reqsn']."'")->update(['pay_status' => 0]);
                    $re = 1;
                }
            }else{
                $msg = '支付成功';
                $re = 1;
            }
            if($re){
                $data = [
                    'pay_status' => $msg
                ];
                echo json_encode(["code"=>1,"msg"=>"查询订单状态成功--".$msg,'data' => $data]);
                exit;
            }

        }else{
            echo json_encode(["code"=>0,"msg"=>$rspArray['msg']]);
            exit;
        }
    }

    function send_post($url, $post_data){

        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    /**
     * 皮皮
     */
    public function SignPPArray(array $array,$appkey){
        ksort($array);
        $blankStr = $this->ToUrlParams($array);
        $blankStr = $blankStr."&Key=".$appkey;// 将key放到数组中一起进行排序和组装
        $sign =  strtolower(md5($blankStr));
        return $sign;
    }

    //环讯订单查询
    public function ipsPaySelect($data){
        #支付配置
        $config["MsgId"] = "0001"; //消息编号
        $config["ReqDate"] = date("YmdHis",time()+0);//商户请求时间
        $config["MerBillNo"] = $data['pay_order_number'];
        $config["Date"] = date('Ymd',$data['create_time']);
        $config["Amount"] = $data['pay_amount'];

        $html_text = $this->buildRequest($config);
        //header("content-type:text/xml;charset=utf-8");
        //echo $html_text;exit;

    }

    //下单请求
    public function buildRequest($payData) {
        try {
            libxml_disable_entity_loader(false);
            $para = $this->BuildXmlReq($payData);

            //发送的请求的报文
            //file_put_contents("./a.xml",$para);
            $wsdl = self::ApiUrl;
            $client=new \SoapClient($wsdl);
            //用$client->__getFunctions()得到两个方法
            //string scanPay(string $scanPayReq)' (length=34)
            //string barCodeScanPay(string $barCodeScanPay)' (length=45)
            $sReqXml = $client->getOrderByMerBillNo($para);
            //响应的到的报文
            //file_put_contents("http://www.game-pk.com/pro.xml",$sReqXml);
            // $fileName =  fopen("http://www.game-pk.com/pro.xml","w+");
            //fwrite($fileName,"123");
            //fclose($fileName);
            print_r($sReqXml);
            return $sReqXml;
        } catch (Exception $e) {
            echo "扫码支付请求异常:" . $e;
        }
        return null;
    }

    //环讯
    //生成XML请求模板
    public function BuildXmlReq($PayData){
        $XmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        $XmlContent .= "<Ips>";
        $XmlContent .= "<OrderQueryReq>";
        $XmlContent .= $this->XmlHeader($PayData);
        $XmlContent .= $this->XmlBody($PayData);
        $XmlContent .= "</OrderQueryReq>";
        $XmlContent .= "</Ips>";
        return $XmlContent;
    }

    //XML头信息
    public function XmlHeader($PayData){
        $XmlContent = "";
        $XmlContent .= "<head>";
        $XmlContent .= "<Version>".self::Version."</Version>";
        $XmlContent .= "<MerCode>".self::MerCode."</MerCode>";
        $XmlContent .= "<MerName>".self::MerName."</MerName>";
        $XmlContent .= "<Account>".self::Account."</Account>";
        $XmlContent .= "<ReqDate>{$PayData['ReqDate']}</ReqDate>";
        $XmlContent .= "<Signature>{$this->Signature($this->XmlBody($PayData),self::MerCode,self::Key)}</Signature>";
        $XmlContent .= "</head>";
        return $XmlContent;
    }
    //XML主体信息
    public function XmlBody($PayData){
        $XmlContent = "";
        $XmlContent .= "<body>";
        $XmlContent .= "<MerBillNo>{$PayData['MerBillNo']}</MerBillNo>";
        $XmlContent .= "<Date>{$PayData['Date']}</Date>";
        $XmlContent .= "<Amount>{$PayData['Amount']}</Amount>";
        $XmlContent .= "</body>";
        return $XmlContent;
    }

    //数字签名
    public function Signature($PayData,$MerCode,$key){
        $signature = md5($PayData.$MerCode.$key);
        //file_put_contents("b.html",$signature);
        $this->Signature = $signature;
        return $signature;
    }

    //快接订单查询
    public function KjPaySelect($data){
        if(!$data['order_number']){
            echo json_encode(["code"=>0,"msg"=>"订单号为空"]);
            exit;
        }
        $config = Config('pay');
        $select_config['merchant_no'] = $config['Kj']['merchant_id'];
        $select_config['trade_no'] = $data['order_number'];
        $select_config['sign_type'] = '1';
        $select_config['sign'] = $this->SignKjArray($select_config,$config['Kj']['key']);//签名

        $url = $config['Kj']['url'] . "/alipay/query_pay";

        $paramsStr = $this->ToUrlParams($select_config);

        $response = $this->request($url, $paramsStr);
        $rspArray = json_decode($response, true);


        if($this->KjvalidSign($rspArray)){
            //var_dump($rspArray);
            if($rspArray['status'] == 1){
                $find = db('promote_deposit')->where("order_number = '".$rspArray['data']['trade_no']."'")->find();
                if($find){
                    if($find['order_number'] != $rspArray['data']['trade_no']){
                        echo json_encode(["code"=>1,"msg"=>"订单号不匹配"]);
                        exit;
                    }

                    if($find['pay_status'] == 0){

                        if($rspArray['data']['status'] == 1){
                            $pay_status = 1;
                            $msg = '支付成功';
                            $re = db('promote_deposit')->where("order_number = '".$rspArray['data']['trade_no']."'")->update(['pay_status' => $pay_status]);
                            if($re){
                                $this->suppOrder($find,session('account')['id']);
                            }
                        }else{
                            $msg = '待支付';
                            $re = 1;
                        }
                    }else{
                        $msg = '支付成功';
                        $re = 1;
                    }
                    if($re){
                        $data = [
                            'pay_status' => $msg
                        ];
                        echo json_encode(["code"=>1,"msg"=>"查询订单状态成功--".$msg,'data' => $data]);
                        exit;
                    }
                }else{
                    echo json_encode(["code"=>0,"msg"=>"订单不存在"]);
                    exit;
                }
            }else{
                echo json_encode(["code"=>0,"msg"=>"请求接口失败"]);
                exit;
            }
        }else{
            echo json_encode(["code"=>0,"msg"=>"验签失败"]);
            exit;
        }
    }

    //发送请求操作仅供参考,不为最佳实践
    public function request($url,$params){
        $ch = curl_init();
        $this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $output = curl_exec($ch);
        curl_close($ch);
        return  $output;
    }


    /**
     * 快接，将参数数组签名
     */
    public function SignKjArray($array,$appkey){
        ksort($array);
        $blankStr = $this->ToUrlParams($array);
        $blankStr = $blankStr."&key=".$appkey;// 将key放到数组中一起进行排序和组装
        //echo $blankStr;echo '---';
        $sign =  md5($blankStr);
        return $sign;
    }

    /**
     * 通联，将参数数组签名
     */
    public function SignArray($array,$appkey){
        $array['key'] = $appkey;// 将key放到数组中一起进行排序和组装
        ksort($array);
        $blankStr = $this->ToUrlParams($array);
        $sign = md5($blankStr);
        return $sign;
    }

    public function ToUrlParams($array)
    {
        $buff = "";
        foreach ($array as $k => $v)
        {
            if($v !== "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    //快接验签
    function KjvalidSign($array){
        $config = Config('pay_config');
        $signRsp = $array['data']["sign"];
        //echo $signRsp;echo '---';
        unset($array['data']["sign"]);

        $sign = $this->SignKjArray($array['data'],$config['Kj']['key']);
        //var_dump($array);exit;
        if($sign == $signRsp){
            return TRUE;
        }else {
            return FALSE;
        }
    }

    //通联验签
    function validSign($array){
        if("SUCCESS"==$array["retcode"]){
            $signRsp = strtolower($array["sign"]);
            $array["sign"] = "";
            $sign =  strtolower($this->SignArray($array, "2018Z0303B"));
            if($sign==$signRsp){
                return TRUE;
            }
            else {
                return FALSE;
            }
        }
        else{
            return FALSE;
        }

        return FALSE;
    }

    /**
     * 随机字符串
     */
    function getRandom($param=100){
        $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = "";
        for($i=0;$i<$param;$i++)
        {
            $key .= $str{mt_rand(0,32)};    //生成php随机数
        }
        return $key;
    }

    //管理员补单记录
    function suppOrder($data,$id){
        if($data){

            $account = db('hands_admin')->where('id',$id)->value('account');

            $ins_data = [
                'order_id' => $data['id'],
                'order_number' => $data['pay_order_number'],
                'select_time' => time(),
                'account' => $account,
            ];
            db('hands_supp_order')->insertGetId($ins_data);
        }
    }
}