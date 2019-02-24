<?php
namespace app\common\model;
use think\Model;
class AlternateImg extends Model
{
	//获取所有的配置项
   public function getAlterImg(){
      $AlterImgRes=$this->where('status',1)->order('sort DESC')->select();
      return $AlterImgRes;
   }
}
