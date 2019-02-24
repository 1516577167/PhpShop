<?php
namespace app\admin\model;
use think\Model;
class Category extends Model
{
    protected $field=true;
    protected static function init()
    {

        category::event('before_update', function ($category) {
            $categoryId=$category->id;
            //新增商品属性  (如果用一下两列的方法，表单提交category_attr这个数据没值的话，就会出错)
            // $categoryAttr=$category->category_attr;
            // $categoryPrice=$category->category_price;
            $categoryData=input('post.');
            //处理商品推荐位
            db('rec_item')->where(['value_type'=>2,'value_id'=>$categoryId])->delete();
            if(isset($categoryData['recpos'])){
                foreach ($categoryData['recpos'] as $k => $v) {
                    db('rec_item')->insert(['recpos_id'=>$v,'value_id'=>$categoryId,'value_type'=>2]);
                }
            }
        });

        category::afterInsert(function($category){
            $categoryData=input('post.');
            $categoryId=$category->id;
            //处理商品推荐位
            if(isset($categoryData['recpos'])){
                foreach ($categoryData['recpos'] as $k => $v) {
                    db('rec_item')->insert(['recpos_id'=>$v,'value_id'=>$categoryId,'value_type'=>2]);
                }
            }
        });
    }
}
