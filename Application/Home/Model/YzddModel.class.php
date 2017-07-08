<?php
namespace Home\Model;
use Think\Model;

class YzddModel extends Model {
    public function __construct(){
        $this->zsh_yzdd_user_hongbao = M('yzdd_user_hongbao','zsh_');
    }
    //手动用微往联盟账号发红包
    public function sendUserHongbao($id){
        $result = false;
        $where['id'] = (int)$id;
        $user_hongbao = $this->zsh_yzdd_user_hongbao->where($where)->find();
        if($user_hongbao['openid'] && $user_hongbao['account'] && ($user_hongbao['status'] == 0 || $user_hongbao['status'] == 2)){
            $cid = $user_hongbao['cid'];
            $openid = $user_hongbao['openid'];
            $account = $user_hongbao['account'];

            $weixin = 'weiwanglianmeng';
            $wxhb = WeixinHongbao($weixin);   //配置微往联盟公众号
            $nonce_str = $wxhb->createNoncestr();   //随机字符串，长度 32 位
            $mch_billno = C('number_zsh').date("Ymd",time()).$wxhb->createNoncestr2();//红包订单号：商户号+年月日+10位随机串
            $mchid = C('mchid_'.$weixin);
            $wxappid = C('appid_'.$weixin);
            $nick_name = C('zsh_yzdd_nick_name_c'.$cid);
            $send_name = C('zsh_yzdd_send_name_c'.$cid);
            $re_openid = $openid;
            $total_amount = $account * 100;
            $min_value = $total_amount;
            $max_value = $total_amount;
            $total_num = 1;
            $wishing = C('zsh_yzdd_wishing_c'.$cid);
            $client_ip = C('zsh_yzdd_client_ip_c'.$cid);
            $act_name = C('zsh_yzdd_act_name_c'.$cid);
            $remark = C('zsh_yzdd_remark_c'.$cid);

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
            $wxhb->setParameter("client_ip", $client_ip); //调用接口的机器 Ip 地址
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
            $this->mylogs($re_openid, $responseXml,$id);//日志记录红包发送结果
            $responseObj = (array)simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

            $data['mch_billno'] = $mch_billno;
            $data['date_modified'] = date('Y-m-d H:i:s');
            if($responseObj['return_code'] == "SUCCESS"){
                //红包发送成功
                $data['status'] = 1;
                $result =  true;
            }else{
                //红包发送失败
                $data['status'] = 2;
            }
            $where['cid'] = $cid;
            $where['openid'] = $openid;
            $this->zsh_yzdd_user_hongbao->where($where)->save($data);
        }
        return $result;
    }

    /**
     * 红包日志
     */
    private function mylogs($openid,$msg,$id){
        $content = date("Y-m-d H:i:s",time())." HongbaoId:".$id." ===> ".$openid."\r\n".$msg."\r\n===================\r\n";
        echo file_put_contents("./Application/Common/Hongbao/logs/".date("Y-m-d").".txt", $content,FILE_APPEND);
    }
}