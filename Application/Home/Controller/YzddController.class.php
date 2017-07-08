<?php
/**
 * 中石化项目：一站到底红包抽奖
 */
namespace Home\Controller;
use Think\Controller;

class YzddController extends Controller {
    private $redis;

    public function _initialize(){
        $this->redis = new \Common\ThinkRedis();
    }

    //中石化一站到底活动入口
    public function index(){
        if($this->checkToken()){
            $this->redis->hSet('zsh_yzdd_user',session('openid'),session('openid_zsh'));
            //进入答题页面
            $web_path = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Template/yzdd/index.html';
            redirect($web_path);
        }else{
            //引导关注图文素材链接
            $subscribe_url = 'http://mp.weixin.qq.com/s?__biz=MzA3NTQ1MDEwMg==&mid=402207423&idx=1&sn=471b115dad185eba92e77ab5b9bce60a&scene=23&srcid=0204p90XWnsrn9AMUNpwDUUV#rd&ADUIN=1058919928&ADSESSION=1454571004&ADTAG=CLIENT.QQ.5425_.0&ADPUBNO=26509';
            redirect($subscribe_url);
        }
    }

    //加密数据验证
    public function checkToken(){
        $result = false;
        $p = I('p');
        $str = base64_decode($p);
        $arr = unserialize($str);
        if(isset($arr['openid']) && $arr['openid'] && isset($arr['openid_zsh']) && $arr['openid_zsh'] && isset($arr['cid']) && $arr['cid'] && isset($arr['token']) && $arr['token']){
            if(md5($arr['openid'].$arr['openid_zsh'].$arr['cid']) == $arr['token']){
                session('cid',$arr['cid']);
                session('openid',$arr['openid']);
                session('openid_zsh',$arr['openid_zsh']);
                $result = true;
            }
        }
        return $result;
    }

    //获取中石化JSSDK配置信息
    public function GetSignPackage(){
        $signPackage = array();
        $jssdk_url = I('post.jssdk_url');
        if($jssdk_url){
            $weixin_zsh = new \Common\Weixin(C('appid_zsh'),C('appsecret_zsh'));
            $signPackage = $weixin_zsh->GetSignPackage($jssdk_url);
        }
        $this->ajaxReturn($signPackage);
    }

    //获取题库
    public function GetQuestions(){
        if(session('openid') && session('cid')){
            //增加答题次数
            $count = (int)$this->redis->hGet('zsh_yzdd_user_answer_count_'.session('cid'),session('openid'));
            $this->redis->hSet('zsh_yzdd_user_answer_count_'.session('cid'),session('openid'),$count + 1);
        }

        //题库，共100道题
        $tiku = $this->redis->get('zsh_yzdd_config_question');;
        $tiku_list = json_decode($tiku,true);
        shuffle($tiku_list); //乱序
        $n = 12; //题目数量
        $list = array_slice($tiku_list,0,$n);  //截取数组
        $result = array(
            'number' => $n,
            'data' => $list
        );
        ob_clean(); //丢弃输出缓冲区中的内容
        $this->ajaxReturn($result);
    }

    //完成答题
    public function FinishAnswer(){
        $result = array();
        //验证用户
        $userinfo = false;
        if(session('openid') && session('cid')){
            $userinfo = $this->redis->hGet('zsh_yzdd_user',session('openid'));
        }
        if(!$userinfo){
            $result['code'] = -2;    //尚未关注
            $this->ajaxReturn($result);exit;
        }
        //验证提交的参数
        $number = (int)I('post.number');
        $score = (int)I('post.score');
        if($number != 12 || $score != 9){
            $result['code'] = -3;    //参数异常
            $this->ajaxReturn($result);exit;
        }
        //添加或更新答题记录
        $this->redis->hSet('zsh_yzdd_user_answer_record_'.session('cid'),session('openid'),date('Y-m-d H:i:s'));
        //验证是否领取过红包
        $user_hongbao = (int)$this->redis->hGet('zsh_yzdd_user_hongbao_'.session('cid'),session('openid'));
        if($user_hongbao){
            $result['code'] = 1;    //已经领过红包
            $this->ajaxReturn($result);exit;
        }
        //验证是否已经老虎机抽奖
        $user_hongbao_nosend = $this->redis->hGet('zsh_yzdd_user_hongbao_nosend_'.session('cid'),session('openid'));
        if($user_hongbao_nosend){
            $result['code'] = 2;    //已经老虎机抽奖
            $this->ajaxReturn($result);exit;
        }
        //验证活动时间
        $StartTime = $this->redis->hGet('zsh_yzdd_config','StartTime');
        $EndTime = $this->redis->hGet('zsh_yzdd_config','EndTime');
        $Now = date('Y-m-d H:i:s');
        if($Now < $StartTime){
            $result['code'] = 3;    //活动尚未开始
            $this->ajaxReturn($result);exit;
        }
        if($Now > $EndTime){
            $result['code'] = 4;    //活动已经结束
            $this->ajaxReturn($result);exit;
        }
        //验证是否分配空白红包
        $user_hongbao_null = $this->redis->hGet('zsh_yzdd_user_hongbao_null_'.session('cid'),session('openid'));
        if($user_hongbao_null){
            $result['code'] = 0;    //已经分配过空白红包
            $this->ajaxReturn($result);exit;
        }
        //验证是否还有红包
        $hongbao_count = (int)$this->redis->hGet('zsh_yzdd_config','HongbaoCount');
        if($hongbao_count <= 0){
            $result['code'] = 5;    //红包已领完
            $this->ajaxReturn($result);exit;
        }
        //分配空白红包
        $this->redis->hSet('zsh_yzdd_user_hongbao_null_'.session('cid'),session('openid'),1);
        //更新总红包数量
        $this->redis->hSet('zsh_yzdd_config','HongbaoCount',$hongbao_count - 1);

        $result['code'] = 0;    //完成答题空白红包分配完成
        $this->ajaxReturn($result);
    }

    //老虎机抽奖
    public function SlotMachine(){
        $result = array();
        //验证用户
        $userinfo = false;
        if(session('openid') && session('cid')){
            $userinfo = $this->redis->hGet('zsh_yzdd_user',session('openid'));
        }
        if(!$userinfo){
            $result['code'] = -2;    //尚未关注
            $this->ajaxReturn($result);exit;
        }
        //验证是否领取过红包
        $user_hongbao = $this->redis->hGet('zsh_yzdd_user_hongbao_'.session('cid'),session('openid'));
        if($user_hongbao){
            $result['code'] = 1;    //已经领过红包
            $this->ajaxReturn($result);exit;
        }
        //验证是否已经老虎机抽奖
        $user_hongbao_nosend = $this->redis->hGet('zsh_yzdd_user_hongbao_nosend_'.session('cid'),session('openid'));
        if($user_hongbao_nosend){
            $result['code'] = 2;    //已经老虎机抽奖
            $this->ajaxReturn($result);exit;
        }
        //验证活动时间
        $StartTime = $this->redis->hGet('zsh_yzdd_config','StartTime');
        $EndTime = $this->redis->hGet('zsh_yzdd_config','EndTime');
        $Now = date('Y-m-d H:i:s');
        if($Now < $StartTime){
            $result['code'] = 3;    //活动尚未开始
            $this->ajaxReturn($result);exit;
        }
        if($Now > $EndTime){
            $result['code'] = 4;    //活动已经结束
            $this->ajaxReturn($result);exit;
        }
        //验证是否分配空白红包
        $user_hongbao_null = $this->redis->hGet('zsh_yzdd_user_hongbao_null_'.session('cid'),session('openid'));
        if(!$user_hongbao_null){
            $result['code'] = 5;    //尚未分配空白红包
            $this->ajaxReturn($result);exit;
        }
        //获取随机红包列表
        $hongbao_list = $this->redis->hGetAll('zsh_yzdd_config_hongbao');
        $list = array();
        foreach($hongbao_list as $key => $val){
            if((int)$val > 0){
                $list[] = $key;
            }
        }
        if(empty($list)){
            $this->redis->hSet('zsh_yzdd_config','HongbaoCount',0);
            $result['code'] = 6;    //红包已领完
            $this->ajaxReturn($result);exit;
        }
        //随机分配红包
        $money = $list[array_rand($list,1)];
        $money_count = (int)$this->redis->hGet('zsh_yzdd_config_hongbao',$money);
        if($money_count > 0){
            $result['money'] = $money;
        }else{
            $result['code'] = -1;    //系统繁忙
            $this->ajaxReturn($result);exit;
        }
        //添加未发送的红包
        $this->redis->hSet('zsh_yzdd_user_hongbao_nosend_'.session('cid'),session('openid'),$money);
        //更新随机红包数量
        $this->redis->hSet('zsh_yzdd_config_hongbao',$money,$money_count-1);

        $result['code'] = 0;    //红包金额分配成功
        $this->ajaxReturn($result);
    }

    //添加分享记录
    public function UserShare(){
        $result = array();
        //验证用户
        $userinfo = false;
        if(session('openid') && session('cid')){
            $userinfo = $this->redis->hGet('zsh_yzdd_user',session('openid'));
        }
        if(!$userinfo){
            $result['code'] = -2;    //尚未关注
            $this->ajaxReturn($result);exit;
        }
        //添加或更新用户分享
        $this->redis->hSet('zsh_yzdd_user_share_'.session('cid'),session('openid'),1);
        //验证是否领取过红包
        $user_hongbao = $this->redis->hGet('zsh_yzdd_user_hongbao_'.session('cid'),session('openid'));
        if($user_hongbao){
            $result['code'] = 1;    //已经领过红包
            $this->ajaxReturn($result);exit;
        }
        //验证是否已经老虎机抽奖
        $user_hongbao_nosend = $this->redis->hGet('zsh_yzdd_user_hongbao_nosend_'.session('cid'),session('openid'));
        if($user_hongbao_nosend){
            $result['code'] = 0;    //已经老虎机抽奖
            $this->ajaxReturn($result);exit;
        }
        $this->ajaxReturn($result);
    }
}