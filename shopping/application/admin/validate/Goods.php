<?php
namespace app\admin\validate;
use think\Validate;
class Goods extends Validate
{
    protected $rule =   [
        'goods_name'  => 'require|unique:goods',
        'category_id'   => 'require',
        'markte_price' => 'require',
        'shop_price' => 'require', 
        'goods_weight' => 'require',   
    ];
    
    protected $message  =   [
        'goods_name.require' => '商品名称必须填写',
        'goods_name.unique'     => '商品名称不能重复',
        'category_id.require'   => '商品所属分类不能为空',
        'markte_price.require' =>'市场价不能为空',
        // 'markte_price.num' =>'市场价必须是数字',
        'shop_price.require' =>'本店价不能为空',
        // 'shop_price.num' =>'本店价必须是数字',
        'goods_weight.require' =>'商品重量不能为空',
        // 'goods_weight.num' =>'商品重量必须是数字',
    ];
}
