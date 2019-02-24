<?php
namespace app\admin\controller;
use think\Controller;
use think\Cache;
class Index extends Controller
{
    public function index()
    {
        return view();
    }

    //清空缓存
    public function clearCache(){
        if(Cache::clear()){
        	$this->success('清除缓存成功！！！');
        }else{
        	$this->error('清除缓存失败！！！');
        }
    }

}
