<?php
namespace Admin\Controller;
use Think\AdminController;
use Think\Page;
use Think\Model;
class HongbaoController extends AdminController {
    public function index(){
		$array['nav_name']='Hongbao';
		$result = array();
		$sql = "SELECT zc.id,zc.title,zu.openid,zu.openid_zsh,zu.nickname,zu.headimgurl,zuh.mch_billno,zuh.account,zuh.status,zuh.type,zuh.date_added,zuh.date_modified 
				FROM zsh_user_hongbao as zuh
				LEFT JOIN zsh_user as zu ON zu.openid=zuh.openid
				LEFT JOIN zsh_config as zc ON zc.id=zuh.cid where 1=1";
		$search = session("search");
   	    if(isset($_POST['id']) &&$_POST['id']) {              //活动id
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
        if(isset($_POST['type']) &&$_POST['type'] or $_POST['type']=='0') {             //红包类型
        	$type = $_POST['type'];
        	$sql .= " and zuh.type='".$type."'";
        	$array['type']=$type;
        } else if (is_array($search) && $search["type"]) {
        	$type = $search['type'];
        	$sql .= " and zuh.type='".$type."'";
        	$array['type']=$type;
        }
		session("search", $array);
		$Model =M();
		$result = $Model->query($sql);
		$array['count'] = count($result);
		$page_size = 20;
		if(isset($_GET['page'])  &&  $_GET['page']){
			$array['nowpage'] =  (int)$_GET['page'];
			$result = array_slice($result, ((int)$_GET['page']-1)*$page_size, $page_size);
		}else{
			$array['nowpage'] = 1;
			$result = array_slice($result,0,$page_size);
		}

		$Page = D('Page', 'Model');
		$link_page = $Page->getPage($array['nowpage'], $array['count'],$url='',$page_size);
		
		$array['link_page']=$link_page;
		$array['hongbaos']=$result;
		$this->assign($array);
		$this->display("hongbao/index");
	}
}