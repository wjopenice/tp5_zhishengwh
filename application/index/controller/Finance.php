<?php
namespace app\index\controller;
use think\Controller;
class Finance extends Controller{
    public function forward_login(){
         $user = hash("sha256","zswhfinance");
         $pass = hash("sha256","zswhroot");
         if(request()->isPut()){
             $u = htmlspecialchars(trim(input("put.account")));
             $p = htmlspecialchars(trim(input("put.password")));
             $username = hash("sha256",$u);
             $userpass = hash("sha256",$p);
             if( ($username == $user) && ($userpass == $pass) ){
                 session("finance",$user);
                 return $this->success("登录成功","/forward_list.html",2);
             }else{
                 return $this->error("账号密码错误","/forward_login.html",2);
             }
         }else{
             return view("forward_login");
         }
     }
    public function forward_list(){
         if(session("finance")){
             $arrData = db("forward_log")->where(['forward_status'=>0])->paginate(10);
             $page = $arrData->render();
             $data = getUserData();
             $this->assign(['arrData'=>$arrData,'page'=>$page,'data'=>$data]);
             return view("forward_list");
         }else{
             return $this->error("请登录","/forward_login.html",2);
         }
     }
    public function forward_edit(){
         if( session("finance")){
             $id = input("get.id");
             if(request()->isPut()){
                 $getdata = input("put.");
                 $status = $getdata['forward_status'];
                 unset($getdata['_method']);
                 unset($getdata['forward_status']);
                 if($status == 0){
                     $getdata['forward_status'] = 1;
                     $bool1 = db("forward_log")->where(['id'=>$id])->update($getdata);
                     $userData = db("user")->where(['business_id'=>$getdata['account']])->find();
                     $pay_cumulative = $userData['cumulative'] - $getdata['forward_price'];
                     $bool2 = db("user")->where(['business_id'=>$getdata['account']])->update(["cumulative"=>$pay_cumulative]);
                     if($bool1 && $bool2){
                         $filename = fopen("./log/pay7.txt","a+");
                         fwrite($filename,"打款成功".date("Y-m-d H:i:s",time()+0)."----".json_encode($getdata)."\r\n");
                         fclose($filename);
                         unset($getdata['forward_status']);
                         $bool = db("finance_log")->insert($getdata);
                         return $this->success("审核成功","/forward_list.html",2);
                     }else{
                         return $this->error("审核失败","/forward_edit.html",2);
                     }
                 }
             }else{
                 $putdata = db("forward_log")->where(['id'=>$id,"forward_status"=>0])->find();
                 $this->assign(['putdata'=>$putdata]);
                 return view("forward_edit");
             }
         }else{
             return $this->error("请登录","/forward_login.html",2);
         }
     }
    public function forward_search(){
        if(request()->isPost()){
            $d = preg_replace("/[%_\s]+/","",ltrim(addslashes(input("d"))));
            $data = db("forward_log")->where(["account"=>$d,"forward_status"=>0])->select();
            return json_encode($data,320);
        }
    }
}