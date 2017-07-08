<?php
namespace Home\Controller;
use Think\Controller;

class OtherController extends Controller {
    //redis用户数据导出sql脚本
    public function redis_yzdd_user(){
        $file = "./Application/Common/Hongbao/logs/1_zsh_yzdd_user".date("Ymd").".sql";
        unlink($file);  //先删除文件
        $redis = new \Common\ThinkRedis();
        $user_list = $redis->hGetAll('zsh_yzdd_user');
        foreach($user_list as $key => $val){
            if($val){
                $openid = $key;
                $openid_zsh = $val;
                $sql = "insert into zsh_yzdd_user(openid, openid_zsh)"
                ." select '". $openid  ."','" . $openid_zsh . "' from dual"
                ." where not exists(select openid from zsh_yzdd_user where openid = '" . $key . "');\n";
                file_put_contents($file, $sql,FILE_APPEND);
            }
        }
        echo 'over';exit;
    }

    //redis答题次数导出sql脚本
    public function redis_yzdd_user_answer_count(){
        $file = "./Application/Common/Hongbao/logs/2_zsh_yzdd_user_answer_count".date("Ymd").".sql";
        unlink($file);  //先删除文件
        $redis = new \Common\ThinkRedis();
        $user_answer_list = $redis->hGetAll('zsh_yzdd_user_answer_count_1');
        foreach($user_answer_list as $key => $val){
            if($val){
                $cid = 1;
                $openid = $key;
                $count = $val;
                $sql = "insert into zsh_yzdd_user_record(cid,openid,count,is_answer,is_share) select"
                    ." '".$cid."','".$openid."','".$count."','0','0'"
                    ." from dual"
                    ." where not exists(select openid from zsh_yzdd_user_record where openid = '".$key."');\n";
                file_put_contents($file, $sql,FILE_APPEND);
            }
        }
        echo 'over';exit;
    }

    //redis用户答题记录数据导出sql脚本
    public function redis_yzdd_user_answer_record(){
        $file = "./Application/Common/Hongbao/logs/3_zsh_yzdd_user_answer_record".date("Ymd").".sql";
        unlink($file);  //先删除文件
        $redis = new \Common\ThinkRedis();
        $user_answer_record_list = $redis->hGetAll('zsh_yzdd_user_answer_record_1');
        foreach($user_answer_record_list as $key => $val){
            if($val){
                $sql = "update zsh_yzdd_user_record set is_answer = '1' where openid = '".$key."';\n";
                file_put_contents($file, $sql,FILE_APPEND);
            }
        }
        echo 'over';exit;
    }

    //redis用户分享数据导出sql脚本
    public function redis_yzdd_user_share(){
        $file = "./Application/Common/Hongbao/logs/4_zsh_yzdd_user_share".date("Ymd").".sql";
        unlink($file);  //先删除文件
        $redis = new \Common\ThinkRedis();
        $user_share_list = $redis->hGetAll('zsh_yzdd_user_share_1');
        foreach($user_share_list as $key => $val){
            if($val){
                $sql = "update zsh_yzdd_user_record set is_share = '1' where openid = '".$key."';\n";
                file_put_contents($file, $sql,FILE_APPEND);
            }
        }
        echo 'over';exit;
    }

    //redis未发送红包数据导出sql脚本
    public function redis_yzdd_user_hongbao_nosend(){
        $file = "./Application/Common/Hongbao/logs/5_zsh_yzdd_user_hongbao_nosend".date("Ymd").".sql";
        unlink($file);  //先删除文件
        $redis = new \Common\ThinkRedis();
        $user_share_list = $redis->hGetAll('zsh_yzdd_user_hongbao_nosend_1');
        foreach($user_share_list as $key => $val){
            if($val){
                $cid = 1;
                $openid = $key;
                $account = $val;
                $status = 0;
                $date_added = $redis->hGet('zsh_yzdd_user_answer_record_1',$openid);

                $sql = "insert into zsh_yzdd_user_hongbao(cid,openid,account,status,date_added) select"
                    ." '".$cid."','".$openid."','".$account."','".$status."','".$date_added."' from dual"
                    ." where not exists(select openid from zsh_yzdd_user_hongbao where openid = '".$openid."');\n";
                file_put_contents($file, $sql,FILE_APPEND);
            }
        }
        echo 'over';exit;
    }

    //发送红包
    private function sendHongbao(){
        $id = (int)I('get.id');
        $Model_yzdd = D('Yzdd');
        $result = $Model_yzdd->sendUserHongbao($id);
        $this->ajaxReturn($result);
    }

    //获取用户基本信息
    private function getUserInfo(){
        $result = false;
        $yzdd_user = M('yzdd_user','zsh_');
        $where['nickname'] = array('EXP','is null');
        $where['headimgurl'] = array('EXP','is null');
        $user = $yzdd_user->where($where)->find();
        if($user['openid_zsh']){
            //获取中石化用户基本信息
            $weixin_zsh = new \Common\Weixin(C('appid_zsh'),C('appsecret_zsh'));
            $userinfo_zsh = $weixin_zsh->getUserinfo($user['openid_zsh']);
            if(is_array($userinfo_zsh) && $userinfo_zsh){
                if(isset($userinfo_zsh['nickname']) && $userinfo_zsh['nickname']){
                    $data['nickname'] = $userinfo_zsh['nickname'];
                }else{
                    $data['nickname'] = '-';
                }
                if(isset($userinfo_zsh['headimgurl']) && $userinfo_zsh['headimgurl']){
                    $data['headimgurl'] = $userinfo_zsh['headimgurl'];
                }else{
                    $data['headimgurl'] = '-';
                }
                $where['openid'] = $user['openid'];
                $where['openid_zsh'] = $user['openid_zsh'];
                $res = M('yzdd_user','zsh_')->where($where)->save($data);
                if($res){
                    $result = $user['id'];
                }
            }
        }
        $this->ajaxReturn($result);
    }

    //创建自定义菜单
    private function create_menu(){
        $weixin_zsh = new \Common\Weixin(C('appid_zsh'),C('appsecret_zsh'));
        $json_data = ' {
                         "button":[
                         {
                              "type":"click",
                              "name":"一站到底",
                              "key":"一站到底"
                          },
                          {
                               "name":"成果",
                               "sub_button":[
                               {
                                   "type":"view",
                                   "name":"劳模",
                                   "url":"http://book.yunzhan365.com/mbyh/dgjl/index.html"
                                },
                                {
                                   "type":"view",
                                   "name":"科技",
                                   "url":"http://book.yunzhan365.com/yxyt/ging/mobile/index.html?from=singlemessage&isappinstalled=0#p=3"
                                },
                                {
                                   "type":"view",
                                   "name":"印象",
                                   "url":"http://book.yunzhan365.com/yxyt/qukv/mobile/index.html?from=singlemessage&isappinstalled=0#p=13"
                                }]
                           },
                           {
                               "name":"互动",
                               "sub_button":[
                               {
                                   "type":"view",
                                   "name":"西北清风",
                                   "url":"http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MzAwOTY5MTA3MA==#wechat_webview_type=1&wechat_redirect"
                                },
                                {
                                   "type":"view",
                                   "name":"新浪微博",
                                   "url":"http://weibo.com/u/3096153244"
                                },
                                {
                                   "type":"view",
                                   "name":"印象",
                                   "url":"http://book.yunzhan365.com/yxyt/qukv/mobile/index.html?from=singlemessage&isappinstalled=0#p=13"
                                }]
                           }]
                     }';
        $result = $weixin_zsh->create_menu($json_data);
        $this->ajaxReturn($result);
    }

    //生成题目数据
    public function make_questions(){
        $zsh_yzdd_question = M('yzdd_question','zsh_');
        $list = $zsh_yzdd_question->select();
        $redis = new \Common\ThinkRedis();
        $redis->set('zsh_yzdd_config_question',json_encode($list));
        echo 'over';exit;
    }
}