<?php
return array(
    //测试账号配置
//    'appid_zsh'    =>  'wx56b30ce4aeaaa992',
//    'appsecret_zsh' =>  '15c12fe4b19a39de1c6b3f2e6c2114cd',

    //微往配置信息
    'appid_weiwang'    =>  'wxc98251dd45c1ef30',
    'appsecret_weiwang' =>  '2f6eaa7b57fff66802748cb952bdfd39',
    'mchid_weiwang' =>  '10018507',     //商户号
    'partnerkey_weiwang' =>  'Ui98uOqJ78Nm84R3lkB34Y4e489sxz24',    //支付的key

    //微往联盟配置信息
    'appid_weiwanglianmeng'    =>  'wx59437eafc22a2410',
    'appsecret_weiwanglianmeng' =>  'b7dc111b8dc48b50913c325de0f6fbb4',
    'mchid_weiwanglianmeng' =>  '1311059201',    //商户号
    'partnerkey_weiwanglianmeng' =>  'U8rnd763laWe2Mnv64c064sdbrx3k1mf',    //支付的key

    //中石化配置信息
    'appid_zsh'    =>  'wx88cb0bb526191dcb',
    'appsecret_zsh' =>  '510d8b1c863548feafcdfeae35fd6b7b',
    'number_zsh' =>  '1010000000',    //中石化项目编号，用于生产红包订单

    //中石化微公益红包配置信息
    'zsh_client_ip_c1'    =>  '115.29.19.161',  //调用接口的机器Ip地址（阿里服务器）
    'zsh_act_name_c1'    =>  '微公益活动',  //活动名称
    'zsh_nick_name_c1'    =>  '中国石化西北油田',  //提供方名称
    'zsh_send_name_c1'    =>  '中国石化西北油田',  //红包发送方名称
    'zsh_wishing_c1'    =>  '感谢参与中国石化西北油田微公益活动！',  //红包祝福语
    'zsh_remark_c1'    =>  '感谢参与',  //红包备注信息
    'zsh_one_money_c1'    =>  1.8,  //单个红包金额

    //中石化一站到底红包配置信息
    'zsh_yzdd_client_ip_c1'    =>  '120.27.142.38',  //调用接口的机器Ip地址（乐客服务器）
    'zsh_yzdd_act_name_c1'    =>  '一站到底知识竞猜',  //活动名称
    'zsh_yzdd_nick_name_c1'    =>  '中国石化西北油田',  //提供方名称
    'zsh_yzdd_send_name_c1'    =>  '中国石化西北油田',  //红包发送方名称
    'zsh_yzdd_wishing_c1'    =>  '感谢参与中国石化西北油田一站到底活动！',  //红包祝福语
    'zsh_yzdd_remark_c1'    =>  '感谢参与',  //红包备注信息

    //view路径配置
    'TMPL_PARSE_STRING' => array(
        '__viewPath__'     =>  __ROOT__.'/Application/Home/View/',
    ),
);