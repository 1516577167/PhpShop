<?php
namespace app\admin\validate;
use think\Validate;
class Nav extends Validate
{
    protected $rule =   [
        'nav_name'  => 'require|unique:nav',
        'pos' => 'require',    
    ];
    
    protected $message  =   [
        'nav_name.require' => '导航名称必须填写!',
        'nav_name.unique'     => '导航名称不能重复!',
        'pos.require'  => '导航位置不能为空!',
    ];
}
