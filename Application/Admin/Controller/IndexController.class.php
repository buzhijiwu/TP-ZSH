<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
		$array['nav_name']='Index';
		if (session('?user_id')) {
			$this->redirect('admin/people/index');
		} else {
			$this->redirect('admin/index/login');
			
		}
	}

	public function login(){
		$array['nav_name']='Login';
		$array['error'] = '0';
		if (isset($_POST['username']) && isset($_POST["password"])) {
			$name = $_POST['username'];
			$password = md5($_POST["password"]);
			
			if ($name == "" || $password == "") {
				$array['error'] = '1';
				$this->assign($array);
				$this->display();exit();
			}
			
			$sql = "select * from zsh_admin where username='".$name."' and password='".$password."'";
			$Model =M();
			$result = $Model->query($sql);
			if($result){
				$row = $result[0];
				session('user_id', $row["id"]); 
				session('username', $row["username"]);
				$this->redirect('admin/people/index');
			}else{
				$array['error']='1';
			}
		}
		$this->assign($array);
		$this->display("index/login");
	}
	public function logout(){
		session_destroy();
		unset($_SESSION);
		$this->redirect('admin/people/index');
	}
}