<?php
namespace app\admin\validate;
use think\Validate;
class Attr extends Validate
{
    protected $rule =   [
        'type_id'  => 'require',
        'attr_name'  => 'require', 
    ];
    
    protected $message  =   [
        'type_id.require' => '所属类型必须存在',
        'attr_name.require' => '属性名称必须填写',
    ];
}
