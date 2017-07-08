<?php
namespace Home\Model;
use Think\Model;

class ZshModel extends Model {
    public function __construct(){
        $this->zsh_config = M('config','zsh_');
        $this->zsh_user = M('user','zsh_');
        $this->zsh_user_answer = M('user_answer','zsh_');
        $this->zsh_user_hongbao = M('user_hongbao','zsh_');
    }

    //添加用户
    public function addUser($data){
        $user = array();
        if(isset($data['openid']) && $data['openid']){
            $user['openid'] = $data['openid'];
        }
        if(isset($data['openid_zsh']) && $data['openid_zsh']){
            $user['openid_zsh'] = $data['openid_zsh'];
        }
        if(isset($data['nickname']) && $data['nickname']){
            $user['nickname'] = $data['nickname'];
        }
        if(isset($data['headimgurl']) && $data['headimgurl']){
            $user['headimgurl'] = $data['headimgurl'];
        }
        $result = $this->zsh_user->add($user);
        return $result;
    }

    //根据openid获取用户信息
    public function getUserinfo($openid){
        $where['openid'] = $openid;
        $result = $this->zsh_user->where($where)->find();
        return $result;
    }

    //添加答题记录
    public function addUserAnswer($data){
        $answer = array();
        if(isset($data['cid']) && $data['cid']){
            $answer['cid'] = $data['cid'];
        }
        if(isset($data['openid']) && $data['openid']){
            $answer['openid'] = $data['openid'];
        }
        if(isset($data['score']) && $data['score']){
            $answer['score'] = $data['score'];
        }
        if(isset($data['use_time']) && $data['use_time']){
            $answer['use_time'] = $data['use_time'];
        }
        if(isset($data['create_time']) && $data['create_time']){
            $answer['create_time'] = $data['create_time'];
        }
        $answer['ranking'] = 0;
        $answer['is_share'] = 0;
        $result = $this->zsh_user_answer->add($answer);
        return $result;
    }

    //获取用户答题记录
    public function getUserAnswer($cid,$openid){
        $where['cid'] = $cid;
        $where['openid'] = $openid;
        $result = $this->zsh_user_answer->where($where)->find();
        return $result;
    }

    //用户分享
    public function updateUserShare($cid,$openid){
        $where['cid'] = $cid;
        $where['openid'] = $openid;
        $data['is_share'] = 1;
        $result = $this->zsh_user_answer->where($where)->save($data);
        return $result;
    }

    //添加用户红包
    public function addUserHongbao($data){
        $user_hongbao = array();
        if(isset($data['cid']) && $data['cid']){
            $user_hongbao['cid'] = $data['cid'];
        }
        if(isset($data['openid']) && $data['openid']){
            $user_hongbao['openid'] = $data['openid'];
        }
        if(isset($data['account']) && $data['account']){
            $user_hongbao['account'] = $data['account'];
        }
        if(isset($data['date_added']) && $data['date_added']){
            $user_hongbao['date_added'] = $data['date_added'];
        }
        if(isset($data['type']) && $data['type']){
            $user_hongbao['type'] = $data['type'];
        }
        $user_hongbao['status'] = 0;
        $result = $this->zsh_user_hongbao->add($user_hongbao);
        return $result;
    }

    //获取用户红包记录
    public function getUserHongbao($cid,$openid){
        $where['cid'] = $cid;
        $where['openid'] = $openid;
        $where['type'] = 0;
        $result = $this->zsh_user_hongbao->where($where)->find();
        return $result;
    }

    //手动用微往账号发红包
    public function sendUserHongbao($cid,$openid){
        $result = false;
        $this->redis = new \Common\ThinkRedis();
        $userinfo = $this->redis->hGet('zsh_user',$openid);
        $user_answer = $this->redis->hGet('zsh_user_answer_'.$cid,$openid);
        $user_share = $this->redis->hGet('zsh_user_share_'.$cid,$openid);
        $user_nosend_hongbao = $this->redis->hGet('zsh_user_nosend_hongbao_'.$cid,$openid);
        if($userinfo && $user_answer && $user_share && $user_nosend_hongbao){
            $user_hongbao = $this->redis->hGet('zsh_user_hongbao_'.$cid,$openid);
            if(!$user_hongbao){
                $user_hongbao = $this->getUserHongbao($cid,$openid);
                if($user_hongbao){
                    if($user_hongbao['status'] == '0' || $user_hongbao['status'] == '2'){
                        $wxhb = WeixinHongbao('weiwang');
                        $nonce_str = $wxhb->createNoncestr();   //随机字符串，长度 32 位
                        $mch_billno = C('number_zsh').date("Ymd",time()).$wxhb->createNoncestr2();//红包订单号：商户号+年月日+10位随机串
                        $mchid = C('mchid_weiwang');
                        $wxappid = C('appid_weiwang');
                        $nick_name = C('zsh_nick_name_c'.$cid);
                        $send_name = C('zsh_send_name_c'.$cid);
                        $re_openid = $openid;
                        $total_amount = C('zsh_one_money_c'.$cid) * 100;
                        $min_value = $total_amount;
                        $max_value = $total_amount;
                        $total_num = 1;
                        $wishing = C('zsh_wishing_c'.$cid);
                        $client_ip = C('zsh_client_ip_c'.$cid);
                        $act_name = C('zsh_act_name_c'.$cid);
                        $remark = C('zsh_remark_c'.$cid);

                        $wxhb->setParameter("nonce_str", $nonce_str); //随机字符串，长度 32 位
                        $wxhb->setParameter("mch_billno", $mch_billno); //红包订单号：商户号+年月日+随机串
                        $wxhb->setParameter("mch_id", $mchid); //商户号
                        $wxhb->setParameter("wxappid", $wxappid);// appid
                        $wxhb->setParameter("nick_name", $nick_name); //提供方名称
                        $wxhb->setParameter("send_name", $send_name); //红包发送者名称
                        $wxhb->setParameter("re_openid", $re_openid); //相对于微往公众号的用户微信串openid
                        $wxhb->setParameter("total_amount", $total_amount); //付款金额，单位分
                        $wxhb->setParameter("min_value", $min_value); //最小红包金额，单位分
                        $wxhb->setParameter("max_value", $max_value); //最大红包金额，单位分
                        $wxhb->setParameter("total_num", $total_num); //红包収放总人数
                        $wxhb->setParameter("wishing", $wishing); //红包祝福语
                        $wxhb->setParameter("client_ip", $client_ip); //调用接口的机器 Ip 地址（ping www.weiwend.com）
                        $wxhb->setParameter("act_name", $act_name); //活劢名称
                        $wxhb->setParameter("remark",$remark); //备注信息
                        //下面是暂未开放的字段
                        $wxhb->setParameter("logo_imgurl", '');//商户logo的url
                        $wxhb->setParameter("share_content",'');//分享文案
                        $wxhb->setParameter("share_url", '');//分享链接
                        $wxhb->setParameter("share_imgurl", '');//分享的图片url

                        $postXml = $wxhb->create_xml();//生成要发送给微信服务器的xml格式
                        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';//请求微信的url
                        $responseXml = $wxhb->postXmlSSLCurl($postXml, $url);
                        $hongbao_count = (int)$this->redis->hGet('zsh_hongbao_count_'.$cid,date('Ymd'));
                        $this->mylogs($re_openid, $responseXml,$hongbao_count);//日志记录红包发送结果
                        $responseObj = (array)simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

                        $data['mch_billno'] = $mch_billno;
                        $data['date_modified'] = date('Y-m-d H:i:s');
                        $where['cid'] = $cid;
                        $where['openid'] = $openid;
                        $where['type'] = 0;
                        if($responseObj['return_code'] == "SUCCESS"){
                            //红包发送成功
                            $this->redis->hSet('zsh_user_hongbao_'.$cid,$openid,serialize($user_hongbao));
                            $this->redis->hDel('zsh_user_nosend_hongbao_'.$cid,$openid);
                            $data['status'] = 1;
                            $this->zsh_user_hongbao->where($where)->save($data);
                            return true;
                        }else{
                            //红包发送失败
                            $data['status'] = 2;
                            $this->zsh_user_hongbao->where($where)->save($data);
                            return false;
                        }
                    }else{
                        $this->redis->hSet('zsh_user_hongbao_'.$cid,$openid,serialize($user_hongbao));
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 红包日志
     */
    private function mylogs($openid,$msg,$hongbao_count){
        $content = date("Y-m-d H:i:s",time())." Count:".$hongbao_count." ===> ".$openid."\r\n".$msg."\r\n===================\r\n";
        echo file_put_contents("./Application/Common/Hongbao/logs/".date("Y-m-d").".txt", $content,FILE_APPEND);
    }
}