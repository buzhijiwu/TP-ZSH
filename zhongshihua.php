<?php
$redirect_activity_url = 'http://zshhd1a2b.aiqibang.net';   //授权成功跳转活动链接

//中石化活动入口
if($_GET['cid']){
    $arr = explode("/",$_GET['cid']);
    //获取活动ID
    $cid = (int)$arr[0];
    if($arr[1] = 'wx_str'){
        $zhongshihua_openid = $arr[2];
    }else{
        $zhongshihua_openid = '';
    }
    if($cid && $zhongshihua_openid){
        //微往联盟授权
        $appid = 'wx59437eafc22a2410';
        $appsecret = 'b7dc111b8dc48b50913c325de0f6fbb4';
        if(isset($_GET['code']) && $_GET['code']){
            $code = $_GET['code'];
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
            $result = https_request($url);
            $jsoninfo = json_decode($result, true);
            if($jsoninfo['openid']){
                $openid = $jsoninfo['openid'];
                $p = create_token($openid,$zhongshihua_openid,$cid);
                $zsh_url = $redirect_activity_url."/index.php?s=/Home/Yzdd/index/p/".$p."/";
                header("Location:".$zsh_url);exit;
            }
        }else{
            $oauth_url = "http://".$_SERVER["HTTP_HOST"]."/zhongshihua.php?cid=".$_GET['cid'];
            $redirect_uri = urlencode($oauth_url);
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=".time()."#wechat_redirect";
            header("Location:".$url);exit;
        }
    }
}
$subscribe_url = 'http://mp.weixin.qq.com/s?__biz=MzA3NTQ1MDEwMg==&mid=402025008&idx=1&sn=7836d7390a020c62bd076eda1df3683b#rd';
header("Location:".$subscribe_url);exit;

//生成加密字符串
function create_token($openid,$zhongshihua_openid,$cid){
    $arr['openid'] = $openid;
    $arr['openid_zsh'] = $zhongshihua_openid;
    $arr['cid'] = $cid;
    $arr['token'] = md5($openid.$zhongshihua_openid.$cid);
    $str = serialize($arr);
    $p = base64_encode($str);
    return $p;
}

//curl请求
function https_request($url, $data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
