<?php
return array(
    'TMPL_PARSE_STRING' => array(
        '__viewPath__'     =>  __ROOT__.'/Application/Admin/View/', // 增加新的view路径
        '__adminPublic__'  =>  __ROOT__.'/Application/Admin/View/public',
    ),
		'URL_MODEL'=>2,
		'URL_ROUTER_ON' => true,
		//自定义标签库的开启
		//"APP_AUTOLOAD_PATH"=>"@.TagLib",
		//"TAGLIB_BUILD_IN"=>"Cx,Game",
		//网站地址不区分大小写
		'URL_CASE_INSENSITIVE' =>true,
		'URL_ROUTE_RULES'=> array(
				'product/index/:p\d' => 'product/index',//展览
		),

        //中石化配置信息
    'number_zsh' =>  '1010000000',    //中石化项目编号，用于生产红包订单

    //微往配置信息
    'appid_weiwang'    =>  'wxc98251dd45c1ef30',
    'appsecret_weiwang' =>  '2f6eaa7b57fff66802748cb952bdfd39',
    'mchid_weiwang' =>  '10018507',     //商户号
    'partnerkey_weiwang' =>  'Ui98uOqJ78Nm84R3lkB34Y4e489sxz24',    //支付的key

    //中石化红包配置信息
    'zsh_c1_client_ip'    =>  '115.29.19.161',  //调用接口的机器Ip地址
    'zsh_c1_act_name'    =>  '微公益奖励红包',  //活动名称
    'zsh_c1_nick_name'    =>  '中国石化西北油田',  //提供方名称
    'zsh_c1_send_name'    =>  '中国石化西北油田',  //红包发送方名称
    'zsh_c1_wishing'    =>  '感谢参与中国石化西北油田微公益活动',  //红包祝福语
    'zsh_c1_remark'    =>  '答题中奖',  //红包备注信息
);