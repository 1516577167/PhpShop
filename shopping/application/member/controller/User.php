<?php
namespace app\member\controller;
use think\Controller;
use app\index\controller\Base;
class User extends Base
{
    public function index(){
        return view();
    }



    public function logout(){
    	session(NULL);
    	cookie('uname',NULL);
    	cookie('upassword',NULL);
    	$this->success('退出成功！',url('member/Account/login'));
    }
}
