<?php
namespace app\index\model;
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
    		$recRes=$this->field('id,mid_thumb,big_thumb,goods_name,shop_price,markte_price')->where($map)->limit($limit)->order('id DESC')->select();
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

   //获取商品会员价
   public function getMemberPrice($goods_id){
      $levelId=session('level_id');
      $levelRate=session('level_rate');
      $goodsInfo=$this->find($goods_id);
      if($levelRate){
        $memberPrice=db('member_price')->where(['mlevel_id'=>$levelId,'goods_id'=>$goods_id])->find();
        if($memberPrice){
          $price=$memberPrice['mprice'];
        }else{
          $levelRate=$levelRate/100;
          $price=$levelRate*$goodsInfo['shop_price'];
        }
      }else{
        $price=$goodsInfo['shop_price'];
      }
      return $price;
   }

   public function getShopPrice($goods_id){
    $goodsInfo=$this->field('shop_price')->find($goods_id);
    return $goodsInfo['shop_price'];
   }

       public function search_goods($cateId){
        $cateTree=new Catetree();
        $ids=$cateTree->childrenids($cateId,db('category'));
        $ids[]=$cateId;
        $where=[
            'g.on_sale'=>1,
            'g.category_id'=>['in',$ids],
        ];
        //价格筛选
        if(input('price')){
          $priceArr=explode('-',input('price'));
          $where['g.shop_price']=array('between',array($priceArr[0],$priceArr[1]));
        }
        //品牌筛选
        if(input('brand')){
          $where['g.brand_id']=input('brand');
        }
        //排序方式
        $orderBy='xl';
        $orderWay='DESC';
        //查询获取数据
        $goodsRes=db('goods')->field("g.id,g.goods_name,g.shop_price,g.mid_thumb,g.big_thumb,IFNULL(SUM(b.goods_num),0) xl,(SELECT COUNT(id) FROM tp_comment c WHERE g.id=c.goods_id) pl,(SELECT group_concat(mid_photo) FROM tp_goods_photo gp WHERE g.id=gp.goods_id) mid_photo,(SELECT group_concat(id) FROM tp_recpos WHERE rec_type=1 AND id IN(SELECT recpos_id FROM tp_rec_item WHERE value_type=1 AND g.id=value_id)) recpos")->alias('g')->join('order_goods b','g.id=b.goods_id AND b.order_id IN(SELECT id FROM tp_order WHERE pay_status=1)','LEFT')->where($where)->group('g.id')->order("$orderBy $orderWay")->paginate(24);
        // dump($goodsRes);die;
        return $goodsRes;
    }
}
