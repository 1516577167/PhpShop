<?php
namespace app\index\controller;
use catetree\Catetree;
class Cate extends Base
{
    public function index($id)
    {
        $cate=db('cate');
        //获取当前栏目及其子栏目的ID，返回数组
        $cateTree=new Catetree();
        $ids=$cateTree->childrenids($id,$cate);
        // dump($ids);die;
        $ids[]=$id;
        $map['cate_id']=['IN',$ids];
        $cacheArtResName=$id.'_artRes';
        if(cache($cacheArtResName)){
            $artRes=cache($cacheArtResName);
        }else{
            $artRes=db('article')->where($map)->select();
            if($this->config['cache']=='是'){
                cache($cacheArtResName,$artRes,$this->config['cache_time']);  
            }
        }
        //当前栏目基本信息
        $cates=$cate->find($id);
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
    	// dump($comCates);die;
    	$this->assign([
    		'comCates'=>$comCates,
    		'helpCates'=>$helpCates,
            'artRes'=>$artRes,//当前栏目及其及栏目文章
            'cates'=>$cates,//当前栏目基本信息
    	]);
    	return view('cate');
    }
}
