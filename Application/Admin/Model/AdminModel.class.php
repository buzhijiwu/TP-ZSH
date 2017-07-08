<?php
namespace Admin\Model;
use Think\Model;

class AdminModel extends Model {
    public function __construct(){
        $this->zsh_admin = M('admin','zsh_');
        $this->zsh_admin_login = M('admin_login','zsh_');
    }

    //获取管理员列表
    public function getAdminList(){
        $result = $this->zsh_admin->select();
        return $result;
    }

    //登录
    public function login($data){
		$user['username']=$data['username'];
		$user['password']=md5($data['password']);
		$result=$this->zsh_admin->where($user)->find();
        return $result;
    }
}