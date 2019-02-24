<?php
namespace app\admin\validate;
use think\Validate;
class Cate extends Validate
{
    protected $rule =   [
        'cate_name'  => 'require|unique:Cate',  
    ];
    
    protected $message  =   [
        'cate_name.require' => '分类名称必须填写',
        'cate_name.unique'     => '分类名称不能重复',
        // 'cate_name.min'=>'栏目分类名称过短',
    ];
}
