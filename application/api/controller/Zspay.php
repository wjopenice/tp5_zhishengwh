<?php
namespace app\api\controller;
use app\common\controller\Api;
class Zspay extends Api
{
    //广州银行
//    const SIGN_KEY = 'z89mu331vh9jxhxeojy4o0w1okpe168c9w0q91t4'; //签名key
//    const MERCHANT_NO = '10000001';//商户号
//    const REQUEST_API = 'http://www.yspay.co/pay/api.php'; //请求地址
    //派易云
//    const PY_ID = 8651;
//    const PY_KEY = "Hq2NuVT8y6iTDcbZZ4PG1t0h1ltDqg22cW9Ja/SU+YMHtv2Lx5";
//    const PY_URL = "http://open.pyicenter.com/appmerchantproxy";
    //青海福彩
    //门店编码：HCST511264651357568
    //商户编码：HCM1533787128899

    public function welcome(){
       $data['data'] = [
           'welcome'=>'Welcome to the zspay interface address !',
           'message'=>'success',
           'code'=>'200 OK'
       ];
       return xml($data);
    }

    public function allindex(){
        $data = $_REQUEST;
        $result = $this->parameterall($data);
        if(isset($result['status'])  && $result['status'] != 1){
            exit(json_encode($result));
        }else{
            $pay_data = $this->select_channel($data['PAY_WAY']);
            if(isset($pay_data['status']) && $pay_data['status'] != 1){
                exit(json_encode($pay_data));
            }else{
                //$data['pay_type'] = $pay_data['pay_type']; //多银行通道开启
                $data['pay_type'] = 1;
                $data['pay_id'] = $pay_data['id'];//通道id
                $data['pay_ip'] = isset($data['pay_ip']) && $data['pay_ip'] ? $data['pay_ip'] :getip();
                $data['goods_id'] = 1;
                $data['goods_name'] = $data['BODY'];
                //$data['pay_amount'] = $this->amountrand($data['pay_amount']);
                //写入数据
                $bool = $this->add_deposit2($result['id'],$data);
                if($bool){
                    //选择第三方支付平台通道
                    switch ($data['CHANNE_TYPE']) {
                        case "全付通道(H5)":$this->ispayall($data);break;
                        case "固额通道(H5)":$this->isjuhuipay($data);break;
                    }
                }else{
                    exit(json_encode(array("status"=>'40019',"msg"=>"充值记录添加失败")));
                }
            }
        }
        exit;
    }

    public function juhuiindex(){
        $data = $_REQUEST;
        $result = $this->parameterall($data);
        if(isset($result['status'])  && $result['status'] != 1){
            exit(json_encode($result));
        }else{
            $pay_data = $this->select_channel($data['PAY_WAY']);
            if(isset($pay_data['status']) && $pay_data['status'] != 1){
                exit(json_encode($pay_data));
            }else{
                //$data['pay_type'] = $pay_data['pay_type']; //多银行通道开启
                $data['pay_type'] = 1;
                $data['pay_id'] = $pay_data['id'];//通道id
                $data['pay_ip'] = isset($data['pay_ip']) && $data['pay_ip'] ? $data['pay_ip'] :getip();
                $data['goods_id'] = 1;
                $data['goods_name'] = $data['BODY'];
                //$data['pay_amount'] = $this->amountrand($data['pay_amount']);
                //写入数据
                $bool = $this->add_deposit2($result['id'],$data);
                if($bool){
                    //选择第三方支付平台通道
                    switch ($data['pay_type']) {
                        case 1:$this->isjuhuipay($data);break;
                    }
                }else{
                    exit(json_encode(array("status"=>'40019',"msg"=>"充值记录添加失败")));
                }
            }
        }
        exit;
    }

    public function isjuhuipay($data){
        //$url = "http://api.b4pay.hk/pay/aliSPay.do";  //上线地址
        $url = "http://api.yyfpay.hk/pay/aliSPay.do"; //测试地址
        //$key = "c33367701511b4f6020ec61ded352059";
        $key = "ab45b45662074548984f90a422cce1b2";//测试KEY
        $payData['body'] = "testinfo";
        //$payData['merchantId'] = "100000000000005";
        $payData['merchantId'] = "1";//测试ID
        $payData['notifyUrl'] = "http://www.zhishengwh.com/api/Succes/info2";
        $payData['subject'] = "testtitle";
        $payData['timestamp'] = $this->getMillisecond();
        $payData['totalAmount'] = "1";
        $payData['tradeNo'] = $this->get_order_number($data['RESQN']);//$resqnData;
        $buffx = "";
        foreach ($payData as $k => $v)
        {
            if($k == 'notifyUrl'){
                $v = urlencode($v);
            }
            $buffx .= $k . "=" . $v . "&";
        }
        $buffx = trim($buffx, "&");
        $signature = $this->shacode($buffx,$key);//签名
        $payData['signature'] = $signature;
        $buff = "";
        foreach ($payData as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $resData = $this->request_ord_pay($url,$buff);
        $arrData = json_decode($resData,true);
        if($arrData['code'] == "-1"){
            exit(json_encode(array("status"=>'40020',"msg"=>"参数不符合")));
        }else{
            $path = ROOT_PATH . 'public' . DS . 'qrcode/';
            $timex = time();
            $saveurl = $path.$timex.".png";
            vendor('phpqrcode.phpqrcode');//引入插件类
            $errorCorrentionLevel = 'L'; //容错级别
            $matrixPoinSize = 4; //生成图片大小 //生成二维码,第二个参数为二维码保存路径
            \QRcode::png($arrData['data']['qr_code'],$saveurl,$errorCorrentionLevel,$matrixPoinSize,2);
            //header("location:".$arrData['data']['qr_code']);
            echo "<script>window.location.href='/api/Zspay/paycode.html?data=".$timex."&price=".$payData['totalAmount']."&tradeNo=".$payData['tradeNo']."';</script>";
        }
    }

    public function paycode(){
        $timex = input("get.data");
        $price = input("get.price");
        $tradeNo = input("get.tradeNo");
        $this->assign(['timex'=>$timex,'price'=>$price,'tradeNo'=>$tradeNo]);
        return view("paycode");
    }

//    public function isguepay($data){
//        $url = "http://api.b4pay.hk/pay/personalCode.do";
//        $secretKey="c33367701511b4f6020ec61ded352059";
//        $merchantId="100000000000005";
//        $timestamp = $this->getMillisecond();
//        $tradeNo = $data['RESQN'];
//        $notifyUrl = urlencode("http://www.zhishengwh.com/api/Succes/info2");
//        $amount = $data['PAY_AMOUNT'];
//        $payWay = "2";
//        $str = "amount={$amount}&merchantId={$merchantId}&notifyUrl={$notifyUrl}&payWay={$payWay}&timestamp={$timestamp}&tradeNo={$tradeNo}";
//        $signature = $this->shacode($str,$secretKey);
//        $reqstr = $str."&signature={$signature}";
//        $resData = $this->request_ord_pay($url,$reqstr);
//        echo  $resData;
//        exit;
//        $res = json_decode($resData,true);
//        if($res['code'] == 1){
//            echo "<script>window.location.href='".$res['data']['codeUrl']."';</script>";
//        }else{
//            exit(json_encode(array("status"=>'40019',"msg"=>"请求失败")));
//        }
//    }

    public function ispayall($data){
        //地址 http://139.198.2.91:38089/forward_jt/service   http://www.cardpower.cn/forward_jtsr/service
        //密钥 7842539C0437B0BC47A4613426DDDE44 r6udlkhy8srzv8wa6faar7znyobtyos5u2ifnfuv
        //商户号 201809050000012 10000325
        $url = "http://www.cardpower.cn/forward_jt/service";
        $key = "7842539C0437B0BC47A4613426DDDE44";
        $pay_way = $data['PAY_WAY'];
        $way = "1";
        switch ($pay_way){
            case "微信": $way = "10";break;
            case "支付宝": $way = "1";break;
            case "QQ支付": $way = "6";break;
            case "快捷": $way = "3";break;
            case "京东": $way = "11";break;
        }
        $service = "hc.createorder";//接口路由
        $merchantcode = "201809050000012";//商户号
        $merchorder_no = $data['RESQN']; //订单号
        $money = "".number_format($data['PAY_AMOUNT'],2,".","").""; //金额
        $paytype = "1"; //1-支付宝H5
        $backurl = "http://www.zhishengwh.com/api/Resdata/index2"; //回调地址
        $subject = $data['BODY']; //商品标题
        $returnurl = "http://www.zhishengwh.com/api/Succes/info2"; //同步跳转地址
        $sendip = $data['pay_ip']; //客户发起支付终端IP
        $transdate = date("YmdHis"); //下单时间	yyyyMMddHHmmss

        $request = str_replace("\\/", "/", json_encode([
            'service'=>$service,
            'merchantcode'=>$merchantcode,
            'merchorder_no'=>$merchorder_no,
            'money'=>$money,
            'paytype'=>$paytype,
            'backurl'=>$backurl,
            'subject'=>$subject,
            'returnurl'=>$returnurl,
            'sendip'=>$sendip,
            'sign'=>"",//签名
            'transdate'=>$transdate],JSON_UNESCAPED_UNICODE));
        $sign = hash("sha512", $request.$key);
        $request2 = json_decode($request,true);
        $request2["sign"]=$sign;
        $request = str_replace("\\/", "/", json_encode($request2,JSON_UNESCAPED_UNICODE));
        //请求接口
        $response=$this->http_post_data($url, $request);
        $arr_response = json_decode($response,true);
        $sign_response = $arr_response["sign"];
        $retcode_response = $arr_response["retcode"];
        $result_response = $arr_response["result"];
        $transurl_response = $arr_response["transurl"];
        $response2 = json_decode($response,true);
        $response2["sign"]="";
        $response3 = str_replace("\\/", "/", json_encode($response2,JSON_UNESCAPED_UNICODE));
        $sign2 = hash("sha512", $response3.$key);
        if(strcasecmp($sign_response,$sign2)==0){
            if(strcmp($retcode_response,"R9")== 0){
                echo "<script>window.location.href='".$transurl_response."';</script>";
            }else{
                echo "ERROR:",$retcode_response,"-",$result_response,"<br>";
            }
        }else{
            echo "签名验证失败<br>";
        }
    }

    public function istp(){
        $url = "https://amtqrcp.testpnr.com/qrcp/E1101";
        $traceNo = "";
        $arrData = [
            'ordAmt'=>"", //金额
            'apiVersion'=>"1.0.0.1", //版本号
            'accSplitBunch'=>"", //分账串
            'payChannelType'=>"", //W1 微信  A1 支付宝  U1 银联
            'appId'=>"",  //微信分配的appId
            'isRaw'=>"", //是否原生 态1．是   0．否
            'openId'=>"", //微信支付 时必填
            'buyerLogonId'=>"", //支付宝买家登录id
            'buyerId'=>"", //支付宝买家唯一id
            'merOperId'=>"kaqilin", //操作员号
            'termOrdId'=>"", //终端订单号
            'outOrdId'=>"",//商户订单号
            'goodsDesc'=>"", //商户描述  UrlEncode 进行编码
            'memberId'=>"310000016000006578",  //商户号
            'sysId'=>"nspos",//机具所属系统
            "prodId"=>"nspos", //机具所属产品
        ];
        $jsonData = json_encode($arrData,328);
        $checkValue = "";

    }

    public function index(){
        //获取请求参数
        $data = $_REQUEST;
        //记录请求参数信息
        recordlog("pay2.txt","reqapi".json_encode($data,320));
        //验证数据
        $result = $this->parameter($data);
        //参数是否通过
        if(isset($result['status'])  && $result['status'] != 1){
            exit(json_encode($result));
        }else{
            //支付数据
            $pay_data = $this->select_channel($data['pay_way']);
            //检测通道
            if(isset($pay_data['status']) && $pay_data['status'] != 1){
                exit(json_encode($pay_data));
            }else{
                //$data['pay_type'] = $pay_data['pay_type']; //多银行通道开启
                $data['pay_type'] = 1;
                $data['pay_id'] = $pay_data['id'];//通道id
                $data['pay_ip'] = isset($data['pay_ip']) && $data['pay_ip'] ? $data['pay_ip'] :getip();
                $data['goods_id'] = 1;
                $data['goods_name'] = $data['body'];
                //$data['pay_amount'] = $this->amountrand($data['pay_amount']);
                //写入数据
                $bool = $this->add_deposit($result['id'],$data);
                if($bool){
                    //选择第三方支付平台通道
                    switch ($data['pay_type']) {
                        //case 2:$this->isgzcp($data);break;
                        //case 1:$this->ispy($data);break;
                        case 1:$this->isqhfc($data);break;
                    }
                }else{
                    exit(json_encode(array("status"=>'40019',"msg"=>"充值记录添加失败")));
                }
            }
        }
    }

    public function isqhfc($data){
        $qhfc['mchId'] = "HCM1533787128899";//商户ID
        $qhfc['storeId'] = "HCST511264651357568";//门店ID
        $arrData = [
            'mchId'=>$qhfc['mchId'],
            'storeId'=>$qhfc['storeId'],
            'outOrderId'=>$this->build_order($data['resqn']),//订单号
            'totalAmt'=>$data['pay_amount'],//金额
            'subject'=>$data['body'],//商户标题
            'body'=>$data['body'],//商品描述
            'returnUrl'=>"http://www.zhishengwh.com/api/Succes/info",//成功跳转
            'notifyUrl'=>"http://www.zhishengwh.com/api/Resdata/index",//异步通知地址
            'version'=> "1.0"
        ];
        $qhfc['jsonData'] = json_encode($arrData,328);//json数据
        $qhfc['sign'] = $this->sha256key($qhfc['jsonData']); //验签
        $url = "http://www.qhfcsj.com/HoneycombPay/order/addWapOrder";
        $qhfcdata = "mchId={$qhfc['mchId']}&storeId={$qhfc['storeId']}&jsonData={$qhfc['jsonData']}&sign={$qhfc['sign']}";
        $strData = $this->request_pay($url,$qhfcdata);
        if($strData['status'] == 200){
            file_put_contents("./log/pay6.txt",$arrData['outOrderId']);
            echo $strData['data']['form'];
        }else{
            exit(json_encode(array("status"=>'40020',"msg"=>"下单失败")));
        }
    }

    public function ispy($data){
        if($data['pay_way'] == "支付宝"){
            $GatewayType = 1;
        }
        if($data['pay_way'] == "微信"){
            $GatewayType = 3;
        }
        //重组数据
        $paydata['partner'] = self::PY_ID;
        //生成订单
        $paydata['out_trade_no'] = $this->build_order($data['resqn']); //$this->build_order_no();
        $paydata['total_fee'] = $data['pay_amount']; //单位元
        $paydata['payment_type'] = $GatewayType;  //1支付宝 3 微信
        $paydata['notify_url'] = 'http://47.104.109.151/api/Aysn/info';//异步通知地址
        $paydata['return_url'] = 'http://47.104.109.151/api/Succes/info';//下行同步通知地址(在支付完成后接口将会跳转到的该地址)。
        $paydata['body'] = $data['body']; //商品说明
        $paydata['sign'] = $this->pysign($paydata,self::PY_KEY) ;//32位小写MD5签名值，UTF-8编码
        $paydata['sign_type'] = "MD5"; //加密类型
        //记录请求参数信息
        recordlog("pay1.txt",json_encode($paydata,320));
        //请求py接口
        $pyurl = "http://open.pyicenter.com/appmerchantproxy?partner={$paydata['partner']}&out_trade_no={$paydata['out_trade_no']}&total_fee={$paydata['total_fee']}&payment_type={$paydata['payment_type']}&notify_url={$paydata['notify_url']}&return_url={$paydata['return_url']}&body={$paydata['body']}&sign={$paydata['sign']}&sign_type={$paydata['sign_type']}";
        //记录相应信息
        recordlog("pay1.txt",json_encode($pyurl,320));
        header("location:$pyurl");
        //$html_text = $this->request_pay_get($pyurl);
        //处理gzcp响应数据
        //$this->response_pay($html_text);
    }

    public function isgzcp($data){

        if($data['pay_way'] == 1){
            $GatewayType = "wxapi";
        }
        if($data['pay_way'] == 2){
            $GatewayType = "alapi";
        }
        //重组数据结构
        $data['bb'] = '1.0';//版本
        $data['shid'] = self::MERCHANT_NO;//商户ID
        $data['ddh'] = $this->build_order_no();//订单号
        $data['je'] = $data['pay_amount'];//金额(必须保留两位小数点，否则验签失败)
        $data['zftd'] = $data['pay_way'];//支付通道 alapi 支付宝 wxapi 微信
        $data['ybtz'] = 'http://47.104.109.151/api/Aysn/info';//异步通知地址
        $data['tbtz'] = 'http://47.104.109.151/api/Succes/info';//支付成功通知地址
        $data['ddmc'] = $data['body'];//订单名称
        $data['ddbz'] = $data['account'];//付款人名称 能识别是那个会员付的款。
        $data['key'] = self::SIGN_KEY; //密钥
        $data['sign'] = $this->gzcpsign($data);//MD5加密串
        //记录请求参数信息
        recordlog("pay1.txt",json_encode($data,320));
        //请求gzcp接口
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        $html_text = $this->request_pay(self::REQUEST_API,$buff);
        //处理gzcp响应数据
        $this->response_pay($html_text);
    }

    public function request_pay_get($url){
        $con = curl_init();
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_URL, $url);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT, 30);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, FALSE);
        $output = curl_exec($con);
        curl_close($con);
        return $output;
    }

    public function response_pay($data){
         echo "<pre>";
         print_r($data);
         echo "</pre>";
    }

    public function shacode($string,$key){
        return hash_hmac("sha1",$string,$key);
    }

    public function getMillisecond() {
        list($microsecond , $time) = explode(' ', microtime()); //' '中间是一个空格
        return (float)sprintf('%.0f',(floatval($microsecond)+floatval($time))*1000);
    }

    public function request_ord_pay($url,$params){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
        $headers = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}
