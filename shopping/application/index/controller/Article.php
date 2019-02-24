<?php
namespace app\index\controller;

class Article extends Base
{
    public function index($id)
    {
    	//当前文章内容
        $cacheName=$id.'_arts';
        if(cache($cacheName)){
            $arts=cache($cacheName);
        }else{
            $arts=db('article')->find($id);
            if($this->config['cache']=='是'){
                cache($cacheName,$arts,$this->config['cache_time']); 
            }
        }
    	//普通左侧栏目分类
        if(cache('comCates')){
            $comCates=cache('comCates');
        }else{
            $comCates=model('cate')->getCate();
            if($this->config['cache']=='是'){
                cache('comCates',$comCates,$this->config['cache_time']);
            }
        }
        //帮助左侧栏目分类
        if(cache('helpCates')){
            $helpCates=cache('helpCates');
        }else{
            $helpCates=model('cate')->showHelpCates();
            if($this->config['cache']=='是'){
                cache('helpCates',$helpCates,$this->config['cache_time']);
            }
        }
    	//面包屑导航获取
    	$position=model('cate')->position($arts['cate_id']);
    	// $position[]=model('cate')->find($arts['cate_id']);
    	// dump($position);die;
    	$this->assign([
    		'comCates'=>$comCates,
    		'helpCates'=>$helpCates,
    		'arts'=>$arts,
    		'position'=>$position,
    	]);
    	return view('article');
    }
}
