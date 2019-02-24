<?php
namespace app\index\model;
use think\Model;
class Conf extends Model
{
	//获取所有的配置项
   public function getConfs(){
      $_confRes=$this->field('ename,value')->select();
      $confRes=[];
      foreach ($_confRes as $k => $v) {
         $confRes[$v['ename']]=$v['value'];
      }
      return $confRes;
   }
}
