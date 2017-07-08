<?php
namespace Admin\Controller;
use Think\AdminController;
use Think\Page;
use Think\Model;
class PeopleController extends AdminController {
	
    public function index(){
		$array['nav_name']='People';
		$result = array();
//		$sqlCount = "SELECT count(1) as num ";
		$sqlResult = "SELECT zc.id,zc.title, zu.openid,zu.openid_zsh,zu.nickname,zu.headimgurl,zua.id as answer_id,zua.score,zua.ranking,zua.use_time,zua.is_share,zua.create_time,zuh.status as reward_hongbao ";
		$sql = " FROM zsh_user_answer as zua
				LEFT JOIN zsh_user as zu ON zu.openid=zua.openid
				LEFT JOIN zsh_config as zc ON zua.cid=zc.id
				LEFT JOIN zsh_user_hongbao as zuh ON (zuh.cid=zua.cid and zuh.openid=zua.openid and zuh.type=1) where 1=1";
		$search = session("search");
		if(isset($_POST['id']) &&$_POST['id']) {     //活动id
			$id = $_POST['id'];
			$sql .= " and zc.id=".$id;
			$array['id']=$id;
		} else if (is_array($search) && $search["id"]) {
			$id = $search['id'];
			$sql .= " and zc.id=".$id;
			$array['id']=$id;
		}
        if(isset($_POST['nickname']) &&$_POST['nickname']) {  //用户昵称
            $nickname = $_POST['nickname'];
            $sql .= " and zu.nickname like '%".$nickname."%' ";
            $array['nickname']=$nickname;
        } else if (is_array($search) && $search["nickname"]) {
            $nickname = $search['nickname'];
            $sql .= " and zu.nickname like '%".$nickname."%' ";
            $array['nickname']=$nickname;
        }
        if(isset($_POST['openid']) &&$_POST['openid']) {    //微往openid
        	$openid = $_POST['openid'];
        	$sql .= " and zu.openid='".$openid."'";
        	$array['openid']=$openid;
        } else if (is_array($search) && $search["openid"]) {
        	$openid = $search['openid'];
        	$sql .= " and zu.openid='".$openid."'";
        	$array['openid']=$openid;
        }
//        $sql.=" order by zua.score desc,zua.use_time asc";
		session("search", $array);
		$Model =M();
		$array['count'] = M('user_answer','zsh_')->count();
//		$array['count'] = $count[0]["num"];
		$page_size = 10;
		if(isset($_GET['page'])  &&  $_GET['page']){
			$array['nowpage'] =  (int)$_GET['page'];
			$sql=$sqlResult.$sql;
			$sql .= " limit ".((int)$_GET['page']-1)*$page_size.",". $page_size;
		}else{
			$array['nowpage'] = 1;
			$sql=$sqlResult.$sql;
			$sql .= " limit 0,".$page_size;
		}	
		$result = $Model->query($sql);
		$Page = D('Page', 'Model');
		$link_page = $Page->getPage($array['nowpage'], $array['count'],$url='',$page_size);
		$array['link_page']=$link_page;
		$array['peoples']=$result;
		$this->assign($array);
		$this->display("people/index");
	}
	public function setRank(){
		$array['nav_name']='People';
		$admin_id=session('user_id');
		$admin=M('admin')->where('id='.$admin_id)->select();
		$admin_name=$admin[0]['username'];
		$array['admin_name']=$admin_name;
		$array['answer_id']=$_GET['id'];            //活动id
		$sql="select zc.id ,zc.title,zu.openid,zu.nickname,zua.ranking,zua.cid from zsh_config as zc,zsh_user as zu,zsh_user_answer as zua where zc.id=zua.cid and zu.openid=zua.openid and zua.id=".$_GET['id'];
		$result=M()->query($sql);
		$array['id']=$result[0]['id'];
		$array['title']=$result[0]['title'];
		$array['openid']=$result[0]['openid'];
		$array['nickname']=$result[0]['nickname'];
		$array['ranking']=$result[0]['ranking'];

        //获取用户奖励红包
        $where['cid'] = $result[0]['cid'];
        $where['openid'] = $result[0]['openid'];
        $where['type'] = 1;
        $user_reward_hongbao = M('user_hongbao','zsh_')->where($where)->find();
        if($user_reward_hongbao){
            $array['account'] = $user_reward_hongbao['account'];
        }else{
            $array['account'] = '';
        }
		$this->assign($array);
		
		$this->display("people/setRank");
	}
	public function set(){
		$array['nav_name']='People';
		$this->assign($array);
		$answer_id=$_POST['answer_id'];
		$data['ranking']=$_POST['rank'];
		$data['account']=$_POST['account'];
		$res = M('user_answer')->where('id='.$answer_id)->save($data);
        if($res !== false){
            $zsh_user_hongbao = M('user_hongbao','zsh_');
            $where['cid'] = $_POST['id'];
            $where['openid'] = $_POST['openid'];
            $where['type'] = 1;
            $user_reward_hongbao = $zsh_user_hongbao->where($where)->find();
            if($user_reward_hongbao){
                $data_save['account'] = $_POST['account'];
                $zsh_user_hongbao->where($where)->save($data_save);
            }else{
                $data_add['cid'] = $_POST['id'];
                $data_add['openid'] = $_POST['openid'];
                $data_add['account'] = $_POST['account'];
                $data_add['status'] = 0;
                $data_add['type'] = 1;
                $data_add['date_added'] = date('Y-m-d H:i:s');
                $zsh_user_hongbao->where($where)->add($data_add);
            }
        }
		$this->success("排名设置成功",U('../admin/People/index'));
	}

    //微往发送红包
    public function sendRedPackage(){
        $cid = I('cid');
        $openid = I('openid');

        $this->redis = new \Common\ThinkRedis();
        $zsh_user_hongbao = M('user_hongbao','zsh_');

        $userinfo = $this->redis->hGet('zsh_user',$openid);
        $user_answer = $this->redis->hGet('zsh_user_answer_'.$cid,$openid);
        $user_share = $this->redis->hGet('zsh_user_share_'.$cid,$openid);

        if($cid && $openid && $userinfo && $user_answer && $user_share){
            $user_hongbao = $this->redis->hGet('zsh_user_reward_hongbao_'.$cid,$openid);
            if(!$user_hongbao){
                $where['cid'] = $cid;
                $where['openid'] = $openid;
                $where['type'] = 1;

                $user_hongbao = $zsh_user_hongbao->where($where)->find();
                if($user_hongbao){
                    if($user_hongbao['status'] == 1){
                        $this->redis->hSet('zsh_user_reward_hongbao_'.$cid,$openid,serialize($user_hongbao));
                        $this->error("该用户已经领取过奖励红包");
                    }else{
                        $wxhb = WeixinHongbao('weiwang');
                        $nonce_str = $wxhb->createNoncestr();   //随机字符串，长度 32 位
                        $mch_billno = C('number_zsh').date("Ymd",time()).$wxhb->createNoncestr2();//红包订单号：商户号+年月日+10位随机串
                        $mchid = C('mchid_weiwang');
                        $wxappid = C('appid_weiwang');
                        $nick_name = C('zsh_c'.$cid.'_nick_name');
                        $send_name = C('zsh_c'.$cid.'_send_name');
                        $re_openid = $openid;
                        $total_amount = $user_hongbao['account'] * 100;
                        $min_value = $total_amount;
                        $max_value = $total_amount;
                        $total_num = 1;
                        $wishing = C('zsh_c'.$cid.'_wishing');
                        $client_ip = C('zsh_c'.$cid.'_client_ip');
                        $act_name = C('zsh_c'.$cid.'_act_name');
                        $remark = C('zsh_c'.$cid.'_remark');

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
                        $this->mylogs($re_openid, $responseXml,true);//日志记录红包发送结果
                        $responseObj = (array)simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);

                        $data['mch_billno'] = $mch_billno;
                        $data['date_modified'] = date('Y-m-d H:i:s');
                        if($responseObj['return_code'] == "SUCCESS"){
                            //红包发送成功
                            $this->redis->hSet('zsh_user_reward_hongbao_'.$cid,$openid,serialize($user_hongbao));
                            $data['status'] = 1;
                            $zsh_user_hongbao->where($where)->save($data);

                            $this->success("奖励红包发放成功");
                        }else{
                            //红包发送失败
                            $data['status'] = 2;
                            $zsh_user_hongbao->where($where)->save($data);

                            $this->error("系统繁忙，请稍后重试");
                        }
                    }
                }else{
                    $this->error("该用户没有进入排名");
                }
            }else{
                $this->error("该用户已经领取过奖励红包");
            }
        }else{
            $this->error("该用户不满足奖励红包发放条件");
        }
    }

    /**
     * 记录奖励红包日志
     */
    private function mylogs($openid,$msg){
        $content = date("Y-m-d H:i:s",time())." ===> ".$openid."\r\n".$msg."\r\n===================\r\n";
        echo file_put_contents("./Application/Common/Hongbao/logs/".date("Y-m-d").".txt", $content,FILE_APPEND);
    }
}