<?php
namespace app\member\validate;

use think\Validate;

class User extends Validate
{
    protected $rule =   [
        'username'  => 'require|max:25|min:4|unique:user',
        'password'  => 'require|max:18|min:6|confirm:confirm_password',
        'email' => 'email|unique:user',    
        'mobile_phone' => 'number|unique:user|length:11',    
        'send_code' => 'number|length:6',  
        'mobileagreement' => 'accepted',
    ];
    
    protected $message  =   [
        'username.require' => '用户名称不能为空',
        'username.max'     => '用户名称最多不能超过25个字符',
        'username.min'     => '用户名称最少不能低于4个字符',
        'username.unique'     => '用户名称已存在',
        'password.require' => '密码不能为空',
        'password.max' => '密码最多不能超过18个字符',
        'password.min' => '密码最少不能低于6个字符',
        'password.confirm' => '两次输入的密码不一致',
        'email.email' => '邮箱格式不正确',
        'email.unique' => '邮箱已存在',
        'mobile_phone.number' => '手机号码只能为数字类型',
        'mobile_phone.unique' => '手机号码已存在',
        'mobile_phone.length' => '手机号码只能为11位数字',
        'send_code.number'   => '邮件验证码必须是数字',
        'send_code.length'   => '邮件验证码不能大于6位',
        'mobileagreement.accepted'  => '请同意许可协议', 
    ];
    
}