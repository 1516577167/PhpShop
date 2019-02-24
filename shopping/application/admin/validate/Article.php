<?php
namespace app\admin\validate;
use think\Validate;
class Article extends Validate
{
	protected $rule =   [
		'title'  => 'require',
		'cate_id'  => 'require', 
		'email'=>'email',
		'link_url'=>'url',   
	];
	
	protected $message  =   [
		'title.require' => '标题必须填写',
		'cate_id.require' => '文章的所属栏目必须填写',
	];
}
