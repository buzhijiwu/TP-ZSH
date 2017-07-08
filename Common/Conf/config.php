<?php
return array(
    //数据库配置
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '115.29.19.161', // 服务器地址
    'DB_NAME'               =>  'thinkphp_zsh',  // 数据库名
    'DB_USER'               =>  'store_root',      // 用户名
    'DB_PWD'                =>  'SZweiwend2014_store',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  'zsh_',    // 数据库表前缀

    //系统配置
    'APP_GROUP_LIST' => 'Home,Admin', // 分组
    'DEFAULT_GROUP' => 'Home', // 默认分组
    'URL_MODEL' => 3, // URL兼容模式
    'URL_CASE_INSENSITIVE' => true, // URL是否不区分大小写 默认区分大小写
    'URL_HTML_SUFFIX'       =>  '',  // URL伪静态后缀设置,例如：".html"

    //Redis缓存配置
    'TP_REDIS_HOST'   =>  '127.0.0.1', //服务器IP
    'TP_REDIS_PORT'   =>  '6379',     //端口
    'TP_REDIS_AUTH'   =>  'redisAuthRoot',    //Redis auth认证(密钥)
);