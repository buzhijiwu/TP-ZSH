<?php
/**
 * 中石化项目：微公益
 */
namespace Home\Controller;
use Think\Controller;

class ZshController extends Controller {
//    private $Model_zsh;
    private $redis;

    public function _initialize(){
        $over_url = 'http://zshgzh.weiwend.net/zhongshihua.html';
        redirect($over_url);exit;
//        $this->Model_zsh = D('Zsh');
//        $this->redis = new \Common\ThinkRedis();
        $this->web_path = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Template/zsh/index.html'; //进入答题页面
        $this->subscribe_url = 'http://mp.weixin.qq.com/s?__biz=MzA3NTQ1MDEwMg==&mid=402025008&idx=1&sn=7836d7390a020c62bd076eda1df3683b#rd'; //引导关注图文素材链接
    }

    //中石化活动入口
    public function index(){
        if($this->checkToken()){
            $userinfo = $this->redis->hGet('zsh_user',session('openid'));
            if(!$userinfo){
                $data['openid'] = session('openid');
                $data['openid_zsh'] = session('openid_zsh');
                $this->redis->hSet('zsh_user',session('openid'),serialize($data));
            }
            redirect($this->web_path);
        }else{
            redirect($this->subscribe_url);
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
        $weixin_zsh = new \Common\Weixin(C('appid_zsh'),C('appsecret_zsh'));
        $signPackage = $weixin_zsh->GetSignPackage($this->web_path);
        $this->ajaxReturn($signPackage);
    }

    //保存得分记录
    public function userAnswer(){
        $score = I('post.score');
        $use_time = I('post.ttime');
        $res = array();
        if(session('openid') && session('cid') && $this->redis->hGet('zsh_user',session('openid'))){
            $user_answer = $this->redis->hGet('zsh_user_answer_'.session('cid'),session('openid'));
            if(!$user_answer){
                $data['cid'] = session('cid');
                $data['openid'] = session('openid');
                $data['score'] = $score;
                $data['use_time'] = $use_time;
                $data['create_time'] = date('Y-m-d H:i:s');
                //用户答题记录添加成功，redis存储
                $this->redis->hSet('zsh_user_answer_'.session('cid'),session('openid'),serialize($data));
                $res['code'] = 0;   //已经参与过答题
            }else{
                $res['code'] = 2;   //已经参与过答题
            }
        }else{
            $res['code'] = 1;   //未关注
        }
        $this->ajaxReturn($res);
    }

    //用户分享
    public function userShare(){
        if(session('openid') && session('cid') && $this->redis->hGet('zsh_user',session('openid'))){
            $user_answer = $this->redis->hGet('zsh_user_answer_'.session('cid'),session('openid'));
            if($user_answer){
                $user_share = $this->redis->hGet('zsh_user_share_'.session('cid'),session('openid'));
                if(!$user_share){
                    $user_share = $this->redis->hSet('zsh_user_share_'.session('cid'),session('openid'),1);
                }
                //分享成功自动添加未发送红包到队列
                if($user_share){
                    $user_hongbao = $this->redis->hGet('zsh_user_hongbao_'.session('cid'),session('openid'));
                    if(!$user_hongbao){
                        $user_nosent_hongbao = $this->redis->hGet('zsh_user_nosent_hongbao_'.session('cid'),session('openid'));
                        if(!$user_nosent_hongbao){
                            $startTime = $this->redis->hGet('zsh_hongbao_time_'.session('cid'),date('Ymd').'_starttime');
                            $endTime = $this->redis->hGet('zsh_hongbao_time_'.session('cid'),date('Ymd').'_endtime');
                            $now = date('Y-m-d H:i:s');
                            if($startTime && $endTime){
                                if($now < $startTime){
                                    $res['code'] = 6;   //今天活动尚未开始
                                }elseif($now > $endTime){
                                    $res['code'] = 5;   //今天活动已经结束
                                }else{
                                    $hongbao_count = (int)$this->redis->hGet('zsh_hongbao_count_'.session('cid'),date('Ymd'));
                                    if($hongbao_count && $hongbao_count > 0){
                                        //分享成功自动添加未发送红包到队列
                                        $this->redis->hSet('zsh_user_nosent_hongbao_'.session('cid'),session('openid'),date('Y-m-d H:i:s'));
                                        $this->redis->hSet('zsh_hongbao_count_'.session('cid'),date('Ymd'),$hongbao_count-1);
                                    }else{
                                        $res['code'] = 5;   //今天红包发放完毕
                                    }
                                }
                            }else{
                                $res['code'] = 7;   //本期活动已经结束
                            }
                        }else{
                            $res['code'] = 4;   //未发送红包已经添加队列
                        }
                    }else{
                        $res['code'] = 3;   //已经领过红包
                    }
                }else{
                    $res['code'] = -1;   //系统繁忙
                }
            }else{
                $res['code'] = 2;   //尚未答题
            }
        }else{
            $res['code'] = 1;   //尚未关注
        }
        ob_clean(); //丢弃输出缓冲区中的内容
        $this->ajaxReturn($res);
    }
}