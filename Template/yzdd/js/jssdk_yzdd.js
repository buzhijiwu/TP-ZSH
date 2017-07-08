/**
 * 中石化一站到底红包抽奖
 */
var wx_imgUrl = 'http://zshhb.weiwend.cn/images/GsDSCfsUIzq6EEcxmQfcwOX5mvpwOvIAwss4qF2fYhwdxCOMBibs6iah2UicyWiaiamPT3glQAqH2Y8JDGmkib1YUuvw.jpg';
var wx_title = '欢迎参与中国石化西北油田一站到底知识竞猜活动';
var wx_desc = '西北油田位于大美新疆，是中石化第二大油田，是我国陆上十大油田之一';
var wx_link = 'http://mp.weixin.qq.com/s?__biz=MzA3NTQ1MDEwMg==&mid=402207423&idx=1&sn=471b115dad185eba92e77ab5b9bce60a&scene=23&srcid=0204p90XWnsrn9AMUNpwDUUV#rd&ADUIN=1058919928&ADSESSION=1454571004&ADTAG=CLIENT.QQ.5425_.0&ADPUBNO=26509';

$.ajax({
    url:'/index.php?s=/Home/Yzdd/GetSignPackage',
    type : 'post',
    timeout: 15000,
    data :{
        jssdk_url: window.location.href
    },
    dataType: 'json',
    success:function(result){
        wx.config({     //微信JSSDK配置
            debug: false,
            appId: result.appId,
            timestamp: result.timestamp,
            nonceStr: result.nonceStr,
            signature: result.signature,
            jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage']   // 所有要调用的 API 都要加到这个列表中
        });
    },
    error:function(){
        alert('服务繁忙，请稍后重试');
    }
});

wx.ready(function(){
    //分享到朋友圈
    wx.onMenuShareTimeline({
        title: wx_title, // 分享标题
        link: wx_link, // 分享链接
        imgUrl: wx_imgUrl, // 分享图标
        success: function () {
            // 用户分享成功后执行的回调函数
            $.ajax({
                url:'/index.php?s=/Home/Yzdd/UserShare',
                type : 'get',
                timeout: 15000,
                data :{},
                dataType: 'json',
                success:function(result){
                    if(result.code == '0'){
                        alert('亲，恭喜您抢到红包，红包将在活动结束后两小时内发送');
                    }
                    if(result.code == '-2'){   //尚未关注
                        alert('请关注公众号，按照流程参与活动');
                        window.location.href = wx_link;
                    }
                },
                error:function(){
                    alert('服务繁忙，请稍后重试');
                }
            });
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });

//分享给朋友
    wx.onMenuShareAppMessage({
        title: wx_title, // 分享标题
        desc: wx_desc, // 分享描述
        link: wx_link, // 分享链接
        imgUrl: wx_imgUrl, // 分享图标
        success: function () {
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
});