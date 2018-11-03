<?php
    // 强类型声明
    declare(strict_types=1);
    // 检查版本号
    if (version_compare(PHP_VERSION, '7.0', '<')) {
        die('require php > 7.0+ ');
    }
    // 取得微秒级13位时间戳
    function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (string)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
    // 时区定义
    date_default_timezone_set('PRC');
    define('RE_VISION', '0.1_Alpha');
    define('TIME_NOW', time());                // 当前时区时间戳
    define('GMT_TIME', TIME_NOW - date('Z'));  // GMT时区时间戳
    define('JS_TIME_NOW', getMillisecond()); // 获取和JS一致得13位时间戳
    // 项目定义
    define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)) . '/');
    //define('APP_PATH', ROOT_PATH . 'application/');
    define('EXTEND_PATH', './extend/');
    define('VENDOR_PATH', './vendor/');
    define('RUNTIME_PATH','./runtime/');
    define('CONF_PATH', './config/');
    define('DATA_PATH', './data/');

    define('CSS_PATH', './public/static/css');
    define('JS_PATH', './public/static/js');
    define('IMG_PATH', './public/static/image');
