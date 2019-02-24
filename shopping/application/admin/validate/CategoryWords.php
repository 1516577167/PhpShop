<?php
namespace app\admin\validate;
use think\Validate;
class CategoryWords extends Validate
{
    protected $rule =   [
        'category_id'  => 'require',
        'word'  => 'require|unique:category_words', 
    ];
    
    protected $message  =   [
        'category_id.require' => '所属类型必须存在',
        'word.require' => '关联词名称必须填写',
        'word.unique'     => '关联词名称不能重复',
    ];
}
