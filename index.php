<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
// 定义应用目录
session_start();
define('APP_PATH', __DIR__ . '/application/');
define('CSS_PATH','/public/static/css');
define('JS_PATH','/public/static/js');
define('IMG_PATH','/public/static/image');
define('INDEX_PATH','/public/static/index');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
