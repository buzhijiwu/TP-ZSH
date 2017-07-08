<?php
//验证消息真实性
if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    echo $_GET['echostr'];exit;
}else{
    //解析微信数据包
    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    if(!empty($postStr)){
        $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $msgtype = strtolower($msg['MsgType']);
        reply($msgtype,$msg);
    }
}

function reply($msgtype,$msg){
    //红包关键字
    $hongbao_key = array('一站到底','活动');
    //关注或者不匹配时回复内容
    $subscribe = '感谢你的关注，回复“一站到底”或“活动”，参与知识竞猜！';

    if($msgtype == 'text'){   //文本消息
        if(in_array($msg['Content'],$hongbao_key)){
            reply_news($msg);
        }else{
            reply_text($msg,$subscribe);
        }
    }elseif($msgtype == 'event'){     //事件消息
        if($msg['Event'] == 'subscribe'){     //关注
            reply_text($msg,$subscribe);
        }elseif($msg['Event'] == 'CLICK'){     //菜单点击推送消息
            if($msg['EventKey'] == '一站到底'){
                reply_news($msg);
            }else{
                reply_text($msg,$subscribe);
            }
        }else{
            reply_text($msg,$subscribe);
        }
    }else{
        reply_text($msg,$subscribe);
    }
}

//回复文本
function reply_text($msg,$Content){
    $CreateTime = time();
    $data = "<xml>
                    <ToUserName><![CDATA[{$msg['FromUserName']}]]></ToUserName>
                    <FromUserName><![CDATA[{$msg['ToUserName']}]]></FromUserName>
                    <CreateTime>{$CreateTime}</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[{$Content}]]></Content>
                </xml>";
    echo $data;exit;
}

//回复图文消息
function reply_news($msg){
    $CreateTime = time();
    $Title = '一站到底·知识竞猜”开始啦，点击下方图片进入考试页面！';
    $Description = '一站到底，就有机会领取红包！';
    $PicUrl = 'http://zshhb.weiwend.cn/images/thum_pic.jpg';
    $Url = 'http://zshzdy1a3b.aiqibang.com/zhongshihua.php?cid=1/wx_str/'.$msg['FromUserName'];
    $data = "<xml>
                <ToUserName><![CDATA[{$msg['FromUserName']}]]></ToUserName>
                <FromUserName><![CDATA[{$msg['ToUserName']}]]></FromUserName>
                <CreateTime>{$CreateTime}</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>1</ArticleCount>
                <Articles>
                <item>
                <Title><![CDATA[{$Title}]]></Title>
                <Description><![CDATA[{$Description}]]></Description>
                <PicUrl><![CDATA[{$PicUrl}]]></PicUrl>
                <Url><![CDATA[{$Url}]]></Url>
                </item>
                </Articles>
                </xml> ";
    echo $data;exit;
}