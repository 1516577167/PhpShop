<?php
namespace app\index\controller;

class Category extends Base
{
    public function index($id)
    {
        //获取品牌信息
        $_brandRes=db('goods')->field('brand_id')->where(['category_id'=>$id])->select();
        $_brandRes=assoc_unique($_brandRes,'brand_id');
        $brandRes=[];
        foreach ($_brandRes as $k => $v) {
            if($v['brand_id']){
                $brandRes[]=db('brand')->field('id,brand_name')->find($v['brand_id']);
            }
        }
        $goodsRes=model('goods')->search_goods($id);
        $this->assign([
            'brandRes'=>$brandRes,
            'goodsRes'=>$goodsRes,
        ]);
    	return view('goods_list');
    }

    public function getCateInfo($id){
        $mCategory=model('Category');
        //获取二级和三级子分类
        $cateRes=$mCategory->getSonCates($id);
        //获取关键字
        $cwRes=db('categoryWords')->where('category_id',$id)->select();
        //获取关联品牌及其推广信息
        $brands=$mCategory->getCategoryBrands($id);
        // dump($brands); die;
    	$data=[];
        $cat='';
        foreach ($cateRes as $k => $v) {
            $cat.='
            <dl class="dl_fore1">
                <dt><a href="'.url('index/Category/index',['id'=>$v['id']],'').'" target="_blank">'.$v["cate_name"].'</a></dt><dd>';
                foreach($v["children"] as $k1=>$v1){
                $cat.='<a href="'.url('index/Category/index',['id'=>$v1['id']],'').'" target="_blank">'.$v1["cate_name"].'</a>';
            }
            $cat.='</dd></dl>
            <div class="item-brands"><ul></ul></div>
            <div class="item-promotions"></div>'; 
        }
        $channels='';
        foreach ($cwRes as $k => $v) {
            $channels.='<a href="'.$v['link_url'].'" target="_blank">'.$v['word'].'</a>';  
        }
        $brandsAdContent='';
        $brandsAdContent='
        <div class="cate-brand">';
            foreach ($brands['brands'] as $k => $v) {
                $brandsAdContent.='
                <div class="img">
                    <a href="'.$v["brand_url"].'" target="_blank" title="'.$v["brand_name"].'"><img src="'.config('view_replace_str.__uploads__').'/'.$v["brand_img"].'"></a>
                </div>';
            }
        $brandsAdContent.='</div>';
        $brandsAdContent.='
        <div class="cate-promotion">
            <a href="'.$brands['promotion']['pro_url'].'" target="_blank"><img width="199" height="97" src="'.config('view_replace_str.__uploads__').'/'.$brands['promotion']['pro_img'].'"></a>
        </div>';
    	$data['topic_content']=$channels;
    	$data['cat_content']=$cat;
    	$data['brands_ad_content']=$brandsAdContent;
        $data['cat_id']=$id;
    	return json($data);
    }


}
