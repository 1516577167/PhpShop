<?php
namespace app\common\model;
use think\Model;
class Cate extends Model
{
	//普通分类
   public function getCate(){
   	//普通分类的顶级分类
   		$comCates=$this->where([
   			'cate_type'=>5,
   			'pid'=>0,
   		])->select();
   		//有二级分类则查询
   		foreach ($comCates as $k => $v) {
   			$comCates[$k]['children']=$this->where([
   			'pid'=>$v['id'],
   		])->select();
   		}
   		return $comCates;
   }
   //网店帮助分类
   public function showHelpCates(){
   		$helpCates=$this->where([
   			'cate_type'=>3,
   			'pid'=>2,
   		])->order('sort DESC')->select();
   		return $helpCates;
   }
   //面包屑导航
   public function position($cateId){
      $data=$this->field("id,pid,cate_name")->select();
      return $this->_position($data,$cateId);
   }

   public function _position($data,$cateId){
      static $arr=[];
      $cates=$this->field("id,pid,cate_name")->find($cateId);
      if(empty($arr)){
         $arr[]=$cates;
      }
      foreach ($data as $k => $v) {
         if($v['id']==$cates['pid']){
            $arr[]=$v;
            $this->_position($data,$v['id']);
         }
      }
      return array_reverse($arr);
   }
}
