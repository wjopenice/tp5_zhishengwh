<?php
namespace app\mobile\controller;

use app\common\controller\Mobile;

class Buss extends Mobile{

    const DATA_AUTH_KEY = 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV';

    public function listx(){
        return view("list");
    }

    public function getlist(){
        $data = db('promote')
            ->alias("P")
            ->field("P.id,P.account,P.create_time,L.level")
            ->join("promote_level L","P.level_id = L.id")
            ->select();
        return json($data);
    }

    public function detail(){
        $id = input("get.id");
        $data['promote'] = db('promote')
            ->alias("P")
            ->field("P.id,P.account,P.nickname,P.mobile_phone,P.email,P.status,L.level")
            ->join("promote_level L","P.level_id = L.id")
            ->where("P.id","=",$id)
            ->find();
        $level = db("promote_level")->field("level")->select();
        $arrlevel = "";
        foreach ($level as $key=>$value){
            $arrlevel .= "'".$value['level']."',";
        }
        $data['level'] = substr($arrlevel,0,-1);
        $this->assign("data",$data);
        return view("detail");
    }

    public function edit(){
        $input = input("post.");
        $id = $input['id'];
        unset($input['btn']);
        unset($input['id']);
        if($input['status'] == "正常"){  $input['status'] = 1; }
        if($input['status'] == "禁用"){  $input['status'] = 0; }
        $arrlevel = db('promote_level')->field("ident")->where("level",$input['level_id'])->find();
        $input['level_id'] = $arrlevel['ident'];
        $bool = db("promote")->where('id',"=",$id)->update($input);
        if($bool){
            $this->success("修改成功","/mobile/buss",2);
        }else{
            $this->error("修改失败");
        }
    }

    public function search(){
        $search = preg_replace("/[%_]+/","",ltrim(input("post.search")));
        $searchData = db("promote")->query("select P.id,P.account,P.create_time,L.level from  tab_promote as P inner join tab_promote_level as L on P.level_id = L.id where P.account like ? ",["%".$search."%"]);
        $this->assign("searchData",$searchData);
        return view("search");
    }

    public function add(){
        if(request()->isPost()){
            $input = input("post.");
            $arrlevel = db('promote_level')->field("ident")->where("level",$input['level_id'])->find();
            $input['level_id'] = $arrlevel['ident'];
            $referee= db('promote')->field('id')->where(["account"=>$input['referee_id'],"identity" =>1])->find();
            $input['referee_id'] = $referee['id'];
            $input['hands_pass'] = $this->think_ucenter_md5($input['password'], self::DATA_AUTH_KEY);
            $input['identity'] = 1;
            $input['create_time'] = time();
            $bool = db("promote")->insert($input);
            if($bool){
                $this->success("添加成功","/mobile/buss",2);
            }else{
                $this->error("添加失败");
            }
        }else{
            $account_key = 'ZB'.time(); //生成商户号
            $levelData = db('promote_level')->field("level")->order('ident asc')->select(); //查询所有档位
            $arrlevel = "";
            foreach ($levelData as $key=>$value){
                $arrlevel .= "'".$value['level']."',";
            }
            $level = substr($arrlevel,0,-1);
            $map['identity'] = 1;
            $account_data = db('promote')->field('id,account')->where($map)->select();
            $arraccount = "";
            foreach ($account_data as $key=>$value1){
                $arraccount .= "'".$value1['account']."',";
            }
            $account_data_all = substr($arraccount,0,-1);
            $this->assign([
                "account_key"=>$account_key,
                'level'=>$level,
                'account_data'=>$account_data_all
            ]);
            return view("add");
        }
    }
    //商户信息详情
    public function rechargedetail(){
        $id = input('get.id');
        $data = [];
        $account = db('promote')->where("id=".$id)->find();
        //充值
        //总充值额
        $where = "promote_id=".$id." and pay_status=1";
        $data['all_money'] = number_format(db('promote_deposit')->where($where)->value('sum(pay_amount)'),2);
        $data['recharge'] = [];

        $data['recharge'] = [
            'pc' => number_format(db('promote_deposit')->where($where . " and pay_way in (1,2,3)")->value('sum(pay_amount)'),2),
            'h5' => number_format(db('promote_deposit')->where($where . " and pay_way in (4,5,6,7) and pay_type!=2")->value('sum(pay_amount)'),2),
            't0' => number_format(db('promote_deposit')->where($where . " and pay_type=2")->value('sum(pay_amount)'),2),
            'h5alipay' => number_format(db('promote_deposit')->where($where . " and pay_way =5 and pay_type!=2")->value('sum(pay_amount)'),2),
            'h5wecatpay' => number_format(db('promote_deposit')->where($where . " and pay_way =4 and pay_type!=2")->value('sum(pay_amount)'),2),

        ];
        $data['paytotal'] = $data['recharge']['pc']+$data['recharge']['h5']+$data['recharge']['t0']+$data['recharge']['h5alipay']+$data['recharge']['h5wecatpay'];

        //总结算额
        $data['all_rech_money'] = number_format(db('promote_deposit')->where($where . " and status = 1")->value('sum(pay_amount)'),2);
        $data['not_rech_money'] = number_format(db('promote_deposit')->where($where . " and status = 0")->value('sum(pay_amount)'),2);

        $sett_already_where = "promote_id=".$id." and status=1 and pay_status = 1";//已结算
        $sett_not_where = "promote_id=".$id." and status=0 and pay_status = 1";//未结算
        $data['sett'] = [
            'already' => [
                'pc' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_way in (1,2,3) and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_wetch' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_way = 4 and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_alipay' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_way = 5 and pay_type!=2")->value('sum(pay_amount)'),2),
                //'t0' => number_format(db('promote_deposit')->where($sett_already_where . " and pay_type=2")->value('sum(pay_amount)'),2),
            ],
            'not' => [
                'pc' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_way in (1,2,3) and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_wetch' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_way = 4 and pay_type!=2")->value('sum(pay_amount)'),2),
                'h5_alipay' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_way = 5 and pay_type!=2")->value('sum(pay_amount)'),2),
                //'t0' => number_format(db('promote_deposit')->where($sett_not_where . " and pay_type=2")->value('sum(pay_amount)'),2),
            ]
        ];

        //提现
        $with_already_where = "promote_id=".$id."  and status=4";//已提现
        $data['all_with_money'] = number_format(db('auth_withdraw')->where($with_already_where)->value('sum(money)'),2);
        $data['not_with_money'] = db('promote')->field("sum(money+h5_wetch_money+h5_alipay_money) not_money")->where("id=".$id)->find();
        $data['with'] = [];

        $data['with'] = [
            'already' => [
                'pc' => number_format(db('auth_withdraw')->where($with_already_where." and auth_way=0")->value('sum(money)'),2),
                'h5_wetch' => number_format(db('auth_withdraw')->where($with_already_where." and auth_way=1")->value('sum(money)'),2),
                'h5_alipay' => number_format(db('auth_withdraw')->where($with_already_where." and auth_way=2")->value('sum(money)'),2),
            ],

            //未提现
            'not'    => [
                'pc' => db('promote')->where("id=".$id)->value('money') + db('promote')->where("id=".$id)->value('t0_money'),
                'h5_wetch' => db('promote')->where("id=".$id)->value('h5_wetch_money'),
                'h5_alipay' => db('promote')->where("id=".$id)->value('h5_alipay_money'),
            ]
        ];


        $this->assign('data',$data);
        $this->assign('account',$account);
        return view("rechargedetail");
    }
    //密码加密
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
}