<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:68:"F:\Visual-NMP-x64\www\test/application/index\view\index\welcome.html";i:1539757285;s:68:"F:\Visual-NMP-x64\www\test\application\index\view\common\header.html";i:1539246596;s:68:"F:\Visual-NMP-x64\www\test\application\index\view\common\footer.html";i:1539246596;}*/ ?>
<!DOCTYPE html>
<html lang="en" style="">
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo $site['title']; ?></title>
    <meta name="keywords" content="<?php echo $site['kewords']; ?>">
    <meta name="description" content="<?php echo $site['description']; ?>">
    <link rel="stylesheet" href="<?=CSS_PATH?>/index/index/base.css">
    <link rel="stylesheet" href="<?=CSS_PATH?>/index/index/public.css">
    <link rel="stylesheet" href="<?=CSS_PATH?>/index/index/index.css">
    <script>
        var u = navigator.userAgent;
        if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {
            window.location.href='/wap.html';
        } else if (u.indexOf('iPhone') > -1 || u.indexOf('MZBrowser') > -1) {
            window.location.href='/wap.html';
        } else if (u.indexOf('Windows Phone') > -1) {
            window.location.href='/wap.html';
        }
    </script>
    <?php if(is_array($zswh_adv) || $zswh_adv instanceof \think\Collection || $zswh_adv instanceof \think\Paginator): if( count($zswh_adv)==0 ) : echo "" ;else: foreach($zswh_adv as $key=>$z_a): if($z_a['position'] == 1): ?>
            <link rel="icon" href="/uploads/<?php echo $z_a['pic_url']; ?>">
        <?php endif; endforeach; endif; else: echo "" ;endif; ?>
</head>
<body class="homeMain">
    <!--  公共导航栏   -->
    <header class="header" id="header">
        <!--  头部登录栏  -->
<div class="loginBox">
    <div class="container cf">
        <a class="fl m_btn"  href="http://www.zhishengwh.com/wap" target="_blank"><i class="dib"></i>玩转H5游戏</a>
        <div class="fr">
            <?php if(!\think\Cookie::get('account')): ?>
            <a class="m_btn login_btn" href="/index/Login/login.html">登录</a>
            <a class="m_btn" href="/index/Login/register.html"> | 注册 |</a>
            <?php else: ?>
            <a class="m_btn login_btn" href="/uc#1">您好，<?=$_COOKIE['account']?> 【会员中心】</a>
            <a class="m_btn" href="/index/Login/logout"> | 退出 |</a>
            <?php endif; ?>
            <a class="m_btn" href="javascript:void(0);">关注我们</a>
        </div>
    </div>
</div>

<!--  搜索栏  -->
<div class="s_height">
    <div class="search container cf">
        <?php if(is_array($zswh_adv) || $zswh_adv instanceof \think\Collection || $zswh_adv instanceof \think\Paginator): if( count($zswh_adv)==0 ) : echo "" ;else: foreach($zswh_adv as $key=>$z_a): if($z_a['position'] == 2): ?>
                <img src="../../../uploads/<?php echo $z_a['pic_url']; ?>" title="<?php echo $z_a['name']; ?>" width="169" height="60" class="logo fl" onclick="location.href='<?php echo $z_a['addr']; ?>';">
            <?php endif; endforeach; endif; else: echo "" ;endif; ?>
        <div class="search_box fr cf">
            <form action="/search" method="post">
            <input type="text" class="fl" name="d"  placeholder="搜索游戏名称" value="">
            <input type="submit" name="btn" id="com_search" style="width: 60px;height: 48px;margin: 0;" class="search_icon fr"  value="">
            <div class="fl hot"><span>热门搜索词：</span><span class="hot_m">绝地求生</span><span class="hot_m">王者荣耀</span><span class="hot_m">世仙王</span><span class="hot_m">阴阳道绝</span></div>
            </form>
        </div>
    </div>
</div>
<?php $type = !empty($_GET['type'])? $_GET['type'] : "index" ; ?>
<!--  导航tab栏  -->
<nav class="tab_t cf">
    <div class="container">
        <ul class="tab_t_wrap cf">
            <li class="sub_tab pr <?php if('index' == $type){ echo 'active'; }?>"><a href="/index?type=index" class="each_tab_link">首页<span class="tab_line dib"></span></a></li>
            <li class="sub_tab pr <?php if('center' == $type){ echo 'active'; }?>"><a href="/center?type=center" class="each_tab_link">游戏中心<span class="tab_line dib"></span></a></li>
            <li class="sub_tab pr <?php if('gift' == $type){ echo 'active'; }?>"><a href="/gift?type=gift" class="each_tab_link">礼包中心<span class="tab_line dib"></span></a></li>
            <li class="sub_tab pr <?php if('convert' == $type){ echo 'active'; }?>"><a href="/convert?type=convert" class="each_tab_link">兑换中心<span class="tab_line dib"></span></a></li>
            <li class="sub_tab pr <?php if('news' == $type){ echo 'active'; }?>"><a href="/news?type=news" class="each_tab_link">资讯中心<span class="tab_line dib"></span></a></li>
            <li class="sub_tab pr <?php if('recharge' == $type){ echo 'active'; }?>"><a href="/recharge?type=recharge" class="each_tab_link">充值中心<span class="tab_line dib"></span></a></li>
            <li class="sub_tab pr <?php if('customer' == $type){ echo 'active'; }?>"><a href="/customer?type=customer" class="each_tab_link">客服中心</a></li>
        </ul>
    </div>
</nav>

    </header>

    <!--  中间主页   -->
    <div class="content_box">

        <!--  banner   -->
        <!--<div class="banner"><img src="<?=IMG_PATH?>/index/common/banner/banner.png" alt=""></div>-->

        <!--   轮播图   -->
        <div class="pr">
            <div class="s_banner slide-box pr">
                <ul class="slide-content">
                    <?php if(is_array($zswh_adv) || $zswh_adv instanceof \think\Collection || $zswh_adv instanceof \think\Paginator): if( count($zswh_adv)==0 ) : echo "" ;else: foreach($zswh_adv as $key=>$z_a): if($z_a['position'] == 10): ?>
                            <li style="background: url(/uploads/<?php echo $z_a['pic_url']; ?>) 50% 0 no-repeat"><a href="<?php echo $z_a['addr']; ?>" target="_blank" title="<?php echo $z_a['name']; ?>"></a></li>
                        <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </ul>
                <ol class="slide-triggers">
                    <?php if(is_array($zswh_adv) || $zswh_adv instanceof \think\Collection || $zswh_adv instanceof \think\Paginator): if( count($zswh_adv)==0 ) : echo "" ;else: foreach($zswh_adv as $key=>$z_a): if($z_a['position'] == 10): ?>
                            <li></li>
                        <?php endif; endforeach; endif; else: echo "" ;endif; ?>
                </ol>
            </div>
        </div>

        <!--    资讯公告    -->
        <div class="notices_box container cf">
            <p class="notices_text fl">资讯公告</p>
            <?php if(is_array($news) || $news instanceof \think\Collection || $news instanceof \think\Paginator): if( count($news)==0 ) : echo "" ;else: foreach($news as $key=>$newx): ?>
            <div class="notices_modular fl cf"><i class="fl">●</i><a href="/newsinfo?id=<?php echo $newx['id']; ?>" target="_blank" class="notices_title fl ofh" title="<?php echo $newx['title']; ?> <?php echo date('Y-m-d',$newx['create_time']); ?>"><?php echo $newx['title']; ?></a><span class="label_new"></span></div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <a class="notices_more fr" href="/news?type=news">查看更多<i></i></a>
        </div>

        <!--   推荐游戏   -->
        <div class="recommend_wrap container">
            <div class="title_box cf">
                <p class="tit_name fl">推荐游戏</p>
                <a class="more_link fr" href="javascript:void(0);" onclick="window.location.reload();"><span>换一组</span><i class="dib"></i></a>
            </div>
            <ul class="recommend_ul cf">
                <?php if(is_array($host) || $host instanceof \think\Collection || $host instanceof \think\Paginator): if( count($host)==0 ) : echo "" ;else: foreach($host as $key=>$vo3): ?>
                <li class="recommend_li fl pr">
                    <img class="recommend_img" src="/uploads/<?php echo $vo3['pic']; ?>" alt="<?php echo $vo3['title']; ?>">
                    <div class="recommend_name">
                        <p class="recommend_t ofh"><?php echo $vo3['title']; ?></p>
                        <p class="recommend_t ofh">领专属豪礼</p>
                    </div>
                    <div class="recommend_n hide">
                        <div class="recommend_name">
                            <p class="recommend_t ofh"><?php echo $vo3['title']; ?></p>
                            <p class="recommend_t ofh">领专属豪礼</p>
                        </div>
                        <div class="recommend_down">
                            <a href="javascript:void(0)" onclick="gift();" class="btn_download">礼包</a>
                            <a href="<?php echo $vo3['addr_code']; ?>" class="btn_download">下载</a>
                        </div>
                    </div>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>

        <!--   游戏排行和热门游戏   -->
        <div class="rank_hot_wrap container cf">
            <div class="rank_game fl">
                <div class="title_box cf">
                    <p class="tit_name fl">游戏排行</p>
                    <a class="more_link fr" href="javascript:void(0);"><span>更多&gt;&gt;</span></a>
                </div>
                <ul class="rank_ul cf">
                    <?php if(is_array($randData) || $randData instanceof \think\Collection || $randData instanceof \think\Paginator): if( count($randData)==0 ) : echo "" ;else: foreach($randData as $k=>$vo2): ?>
                    <li class="sub_rank_li cf">
                        <div class="sub_rank_num <?php if($k+1 == 1 || $k+1 == 2 || $k+1 == 3){ echo 'rank_front'; } ?>  fl"><?=$k+1?></div>
                        <div class="sub_rank_name ofh fl"><?php echo $vo2['title']; ?></div>
                        <div class="rank_info fl cf hide">
                            <img src="/uploads/<?php echo $vo2['icon']; ?>" class="rank_img fl" />
                            <div class="rank_right fl">
                                <p class="rank_name ofh"><?php echo $vo2['info']; ?></p>
                                <p class="cf rank_operate"><a href="<?php echo $vo2['addr_code']; ?>" class="fl btn_rank_down">下载</a><a href="javascript:void(0)" onclick="gift();" class="fl">礼包</a></p>
                            </div>
                        </div>
                        <div class="sub_rank_type ofh fr"><?php echo $vo2['type']; ?></div>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>
            <div class="hot_game fl">
                <div class="title_box cf">
                    <p class="tit_name fl">热门游戏</p>
                    <a class="more_link fr" href="javascript:void(0);"><span>更多&gt;&gt;</span></a>
                </div>
                <ul class="hot_ul cf">
                    <?php if(is_array($apk) || $apk instanceof \think\Collection || $apk instanceof \think\Paginator): if( count($apk)==0 ) : echo "" ;else: foreach($apk as $key=>$vo): ?>
                    <li class="sub_hot_game cf">
                        <div class="game_img fl"><img src="/uploads/<?php echo $vo['icon']; ?>" onclick="location.href='/detail?type=center&id=<?php echo $vo['id']; ?>#1'"></div>
                        <div class="hot_content fl">
                            <p class="sub_hot_name ofh" onclick="location.href='/detail?type=center&id=<?php echo $vo['id']; ?>#1'"><?php echo $vo['title']; ?></p>
                            <p class="sub_hot_type_box ofh"><span class="sub_hot_type"><?php echo $vo['type']; ?></span><span>|</span><span class="sub_hot_big"><?=rand("10","99")?>.<?=rand("00","99")?>MB</span></p>
                            <p class="operate_btn_box cf">
                                <a href="<?php echo $vo['addr_code']; ?>"  class="btn_down_hot fl">下载</a>
                                <a href="javascript:void(0)" onclick="gift();" class="fl">礼包</a>
                            </p>
                        </div>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>
        </div>

        <!--    热门礼包    -->
        <div class="hot_gift_wrap container">
            <div class="title_box cf">
                <p class="tit_name fl">热门礼包</p>
                <a class="more_link fr" href="javascript:void(0);"><span>更多&gt;&gt;</span></a>
            </div>
            <ul class="hot_ul cf">
                <?php if(is_array($apk1) || $apk1 instanceof \think\Collection || $apk1 instanceof \think\Paginator): if( count($apk1)==0 ) : echo "" ;else: foreach($apk1 as $key=>$vo1): ?>
                <li class="sub_hot_gift cf">
                    <div class="game_img fl"><img src="/uploads/<?php echo $vo1['icon']; ?>" onclick="location.href='/detail?type=center&id=<?php echo $vo1['id']; ?>#2'"></div>
                    <div class="hot_content fl">
                        <p class="sub_gift_name ofh" onclick="location.href='/detail?type=center&id=<?php echo $vo1['id']; ?>#2'"><?php echo $vo1['title']; ?></p>
                        <p class="sub_hot_gift_box ofh">新手礼包</p>
                        <p class="operate_btn_box cf">
                            <a href="<?php echo $vo1['addr_code']; ?>"  class="get_gift_btn"><i></i><span>领取</span></a>
                        </p>
                    </div>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>

        <!--   公共底部    -->
        <div id="footer">
            <!--   友情链接   -->
<div class="f_link_box container">
    <div class="title_box cf">
        <p class="tit_name fl">友情链接</p>
    </div>
    <ul class="link_content_ul cf">
        <?php if(is_array($link) || $link instanceof \think\Collection || $link instanceof \think\Paginator): $i = 0; $__LIST__ = $link;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <li class="each_sub_link fl"><a href="<?php echo $vo['link_url']; ?>" class="link_a" target="_blank"><?php echo $vo['title']; ?></a></li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </ul>
</div>

<!--  版权信息   -->
<div class="footer_box">
    <div class="container cf">
        <div class="footerContentWrap fl">
            <div class="subCopy aboutUs">
                <a href="javascript:void(0)">关于我们</a>
                <span>|</span>
                <a href="javascript:void(0)">商务合作</a>
                <span>|</span>
                <a href="javascript:void(0)">合作伙伴</a>
                <span>|</span>
                <a href="javascript:void(0)">客服帮助</a>
                <span>|</span>
                <a href="javascript:void(0)">注册协议</a>
            </div>
            <div class="subCopy">
                <a href="javascript:void(0)">防沉迷系统</a>
                <span>|</span>
                <a href="javascript:void(0)">未成年家长监护工程</a>
                <span>|</span>
                <a href="javascript:void(0)">《网络游戏服务格式化协议必备条款》</a>
            </div>
            <div class="subCopy">
                <p><?php echo $site['copyright']; ?></p>
            </div>
            <div class="subCopy">
                <p>网站备案：<?php echo $site['icpb']; ?></p>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    (function (w, d, s, i, v, j, b) {
        w[i] = w[i] || function () {
                (w[i].v = w[i].v || []).push(arguments)
            };
        j = d.createElement(s),
            b = d.getElementsByTagName(s)[0];
        j.async = true;
        j.charset = "UTF-8";
        j.src = "https://www.v5kf.com/158566/v5kf.js";
        b.parentNode.insertBefore(j, b);
    })(window, document, "script", "V5CHAT");
</script>
<script>window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"1","bdSize":"16"},"slide":{"type":"slide","bdImg":"0","bdPos":"right","bdTop":"177.5"},"image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"致晟游戏分享：","viewSize":"16"},"selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];</script>
        </div>

    </div>

    <script src="<?=JS_PATH?>/index/common/jquery.js"></script>
    <script src="<?=JS_PATH?>/index/common/jquery.ext.js"></script>
    <script src="<?=JS_PATH?>/index/common/public.js"></script>

    <script>
        $(function(){
            //轮播图
            //banner
            $(".slide-box").hbySlide();
            /*var myApi = new Myapi();
            myApi.JSON.lagout($('.s_banner'),4000,20);*/
        });
        function gift() {
            alert('暂无礼包');
        }
        function downapk() {
            alert('暂无下载');
        }

    </script>
</body>
</html>
<script>(function(){
    var src = (document.location.protocol == "http:") ? "http://js.passport.qihucdn.com/11.0.1.js?fd2172bb5380362ac886caaa6cd8f92b":"https://jspassport.ssl.qhimg.com/11.0.1.js?fd2172bb5380362ac886caaa6cd8f92b";
    document.write('<script src="' + src + '" id="sozz"><\/script>');
})();
</script>