<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    //接口
    'api$'                      => 'api/Zspay/welcome',
    //pc前台
    'index$'                    =>  'index/Index/welcome',
    'center$'                   =>  'index/Index/gmct',
    'convert$'                  =>  'index/Index/convert',
    'customer$'                 =>  'index/Index/customer',
    'gift$'                     =>  'index/Index/gift',
    'news$'                     =>  'index/Index/news',
    'newsinfo$'                 =>  'index/Index/newsinfo',
    'recharge$'                 =>  'index/Index/recharge',
    'centerajax$'               => 'index/Index/gmctajax',
    'search$'                   => 'index/Index/search',
    'customer/info$'            => 'index/Index/customerinfo',
    'detail$'                   => 'index/Index/gmct_detail',
    //提现系统
    'put_login$'                => 'index/Outforward/put_login',
    'forward$'                  => 'index/Outforward/put_forward',
    'select$'                   => 'index/Outforward/put_select',
    'forward/list$'             => 'index/Outforward/put_list',
    'forward/put_recharge$'     => 'index/Outforward/put_recharge',
    'forward/success$'          => 'index/Outforward/put_success',
    'put_logout$'               => 'index/Outforward/put_logout',
    //财务结算系统
    'forward_login$'            => 'index/Finance/forward_login',
    'forward_list$'             => 'index/Finance/forward_list',
    'forward_edit$'             => 'index/Finance/forward_edit',
    'forward_search$'           => 'index/Finance/forward_search',
    //个人中心
    'uc$'                       => 'index/Index/uc',
    '/uc/ajaxpwd$'              => 'index/Index/ajaxpwd',
    //pc后台
    'admin$'                    =>  'admin/Login/welcome',
    'admin/index$'              =>  'admin/Index/welcome',
    'admin/logout$'             =>  'admin/Login/logout',
    //h5前台
    'phone$'                    =>  'phone/Index/welcome',
    //pc支付
    'pay$'                      =>  'pay/Index/index',
    'pay/welcome'               =>  'pay/Index/welcome',
    'pay/login$'                =>  'pay/Login/login',
    'pay/logout$'               =>  'pay/Login/logout',
    'pay/adminupass$'           =>  'pay/User/savepass',
    //left_nav
    'apromote$'                 => 'pay/Administration/promote',//所有商户列表页
    'black$'                    => 'pay/Black/index',
    'arecharge$'                => 'pay/Administration/index',  //所有充值记录页
    'supplement$'               => 'pay/Supplement/index',
    'fiels$'                    => 'pay/Fiels/index',
    'stall$'                    => 'pay/Stall/index',
    'jurisd$'                   => 'pay/Jurisdiction/index',
    'class$'                    => 'pay/Jurisdiction/type',
    'role$'                     => 'pay/Role/index',
    'user$'                     => 'pay/User/index',
    'rechargestat$'             => 'pay/Stat/recharge',
    'witchstat$'                => 'pay/Stat/with',
    'notice$'                   => 'pay/Notice/index',
    'asub$'                     => 'pay/Substitute/index',//代付
    'aprofit$'                  => 'pay/Profit/index',
    'awithdraw$'                => 'pay/Administration/with',//提现记录页
    'payine$'                   => 'pay/Pay/index',//接口列表
    'bussinfo$'                 => 'pay/Administration/bussinfo',//所有商户列表页
    'addbussinfo$'              => 'pay/Administration/addbussinfo',//所有添加商户列表
    //h5支付
    'mobile$'                        => 'mobile/Login/welcome', //移动后台登录
    'mobile/logout$'                 => 'mobile/Login/logout', //移动后台退出
    'mobile/index$'                  => 'mobile/Index/index', //移动后台首页
    'mobile/buss$'                   => 'mobile/Buss/listx',//所有商户列表页
    'mobile/buss/detail$'            =>'mobile/Buss/detail', //单个商户信息
    'mobile/buss/add$'               =>'mobile/Buss/add', //添加商户
    'mobile/buss/rechargedetail$'    =>"mobile/Buss/rechargedetail",//商户充值
    'mobile/buss/getlist$'           =>"mobile/Buss/getlist",
    'mobile/gear$'                   =>"mobile/Gear/listx",//档位列表
    'mobile/interfaces$'             =>"mobile/Interfaces/listx",//接口列表
    'mobile/recharges$'              =>"mobile/Recharges/listx",//充值列表
    //wap
    'wap$'                           =>"wap/Index/welcome",
    'wap/gmct$'                      =>"wap/Index/gmct",
    'wap/info$'                      =>"wap/Index/info",
    'wap/gift$'                      =>"wap/Index/gift",
    'wap/customer$'                  =>"wap/Index/customer",
    'wap/login$'                     =>"wap/Login/welcome",//登录页面信息
    'wap/register$'                  =>"wap/Login/register",//账号注册
    'wap/logout$'                    =>"wap/Login/logout",//退出登录
    'wap/forget$'                    =>"wap/Login/forget_pwd",//忘记密码
    'wap/search$'                    =>"wap/Index/search",//搜索页面
    'wap/rank$'                      =>"wap/Index/game_rank",//游戏排行列表
    'wap/gift_info$'                 =>"wap/Index/game_gift",//游戏礼包详情
    'wap/down$'                      =>"wap/Index/game_down",//游戏下载
    'wap/about$'                     =>"wap/Index/about_us",//关于我们
    'wap/capital$'                   =>"wap/Index/my_capital",//我的资金
    'wap/record$'                    =>"wap/Index/record",//消费记录
    'wap/recharge$'                  =>"wap/Index/recharge",//消费记录
    'wap/modify$'                    =>"wap/Index/modify_pwd",//修改密码
    'wap/msg$'                       =>"wap/Index/msg",//消息通知
    'wap/msg_info$'                  =>"wap/Index/msg_info",//消息详情
    'wap/problem$'                   =>"wap/Index/problem",//常见问题
    'wap/problem_info$'              =>"wap/Index/problem_info",//常见问题
    'wap/problem_list$'              =>"wap/Index/problem_list",//问题列表
    'wap/feedback$'                  =>"wap/Index/feedback",//意见反馈
];
