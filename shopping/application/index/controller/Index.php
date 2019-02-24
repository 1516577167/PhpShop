<?php
namespace app\index\controller;
use think\Cache;
class Index extends Base
{
    public function index()
    {

   // dump($this->config);die;
        //调用公告及促销文章
        if(cache('anmentRes')){
            $anmentRes=cache('anmentRes');
        }else{  
            $anmentRes=model('Article')->getArts(21,3);//公告
            if($this->config['cache']==1){
                cache('anmentRes',$anmentRes,$this->config['cache_time']);
            }
        }
        // cache(NULL);
        //促销
         if(cache('salesRes')){
            $salesRes=cache('salesRes');
        }else{  
            $salesRes=model('Article')->getArts(30,3);//促销
             if($this->config['cache']==1){
                cache('salesRes',$salesRes,$this->config['cache_time']);
            }
        }
    	//热卖商品
    	//获取首页大模块顶级分类数据开始
        if(cache('categoryRes')){
            $categoryRes=cache('categoryRes');
        }else{
            $categoryRes=model('category')->getRecCategorys(5,0); //首页推荐 推荐位的顶级分类
            foreach ($categoryRes as $k => $v) {
                //获取顶级分类下被设为 首页推荐的子分类
                $categoryRes[$k]['children']=model('category')->getRecCategorys(5,$v['id']);
                //获取二级栏目及其子栏目下的精品推荐商品，用于首页显示
                foreach ($categoryRes[$k]['children'] as $k1 => $v1) {
                    $categoryRes[$k]['children'][$k1]['bestGoods']=model('Goods')->getIndexRecposGoods($v1['id'],7);
                }
                //获取新品推荐
                $categoryRes[$k]['newRecGoods']=model('Goods')->getIndexRecposGoods($v['id'],4);
                //获取该顶级分类相关的品牌信息
                $categoryRes[$k]['brands']=model('category')->getCategoryBrands($v['id']);
                //获取当前顶级栏目左侧图信息
                $categoryRes[$k]['leftImgs']=model('CategoryAd')->getCategoryAd($v['id']);
            }
             if($this->config['cache']=='是'){
                cache('categoryRes',$categoryRes,$this->config['cache_time']);
            }
        }

        //调用首页商品
        if(cache('indexGoodsRes')){
            $indexGoodsRes=cache('indexGoodsRes');
        }else{
            $indexGoodsRes=model('goods')->getResposGoods(8,20);
            if($this->config['cache']=='是'){
                cache('indexGoodsRes',$indexGoodsRes,$this->config['cache_time']); 
            }
        }
        // Cache::clear();
        //调用首页轮播图数据
        if(cache('alternateImgRes')){
            $alternateImgRes=cache('alternateImgRes');
        }else{
            $alternateImgRes=model('AlternateImg')->getAlterImg();
            if($this->config['cache']=='是'){
                cache('alternateImgRes',$alternateImgRes,$this->config['cache_time']);
            }
        }
    	$this->assign([
    		'show_nav'=>1,//首页导航默认展开，其他页面默认收缩
    		'categoryRes'=>$categoryRes,//首页大分类数据
            'indexGoodsRes'=>$indexGoodsRes,//首页商品
            'anmentRes'=>$anmentRes,
            'salesRes'=>$salesRes,
            'alternateImgRes'=>$alternateImgRes,
    	]);
    	return view();
    }

}
