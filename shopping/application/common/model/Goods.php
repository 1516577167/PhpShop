<?php
namespace app\common\model;
use think\Model;
use catetree\Catetree;
class Goods extends Model
{	
	//获取指定推荐位里的商品
   public function getResposGoods($recposId,$limit=''){
   			$_hotIds=db('rec_item')->where(['value_type'=>1,'recpos_id'=>$recposId])->select();
    		$hotIds=[];
    		foreach ($_hotIds as $k => $v) {
    			$hotIds[]=$v['value_id'];
    		}
    		$map['id']=['IN',$hotIds];
    		$recRes=$this->field('id,mid_thumb,goods_name,shop_price,markte_price')->where($map)->limit($limit)->order('id DESC')->select();
    		return $recRes;
   }

   //获取首页一、二级分类下的所有推荐商品
   public function getIndexRecposGoods($cateId,$recposId){
        $catetree=new Catetree();
        $sonIds=$catetree->childrenids($cateId,db('category'));
        $sonIds[]=$cateId;
        //2、获取新品推荐位里符合条件的商品信息
        $_recGoods=db('rec_item')->where(['value_type'=>1,'recpos_id'=>$recposId])->select();
        $recGoods=[];
        foreach ($_recGoods as $kk => $vv) {
          $recGoods[]=$vv['value_id'];
        }
        $map['category_id']=['IN',$sonIds];
        $map['id']=['IN',$recGoods];
        // dump($map);
        $goodsRes=db('goods')->where($map)->limit(6)->order('id DESC')->select();
        return $goodsRes;
   }
}
