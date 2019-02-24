<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
Route::rule('cate/:id','index/Cate/index','get',['ext'=>'html|htm'],['id'=>'\d{1,3}']);
Route::rule('article/:id','index/Article/index','get',['ext'=>'html|htm'],['id'=>'\d{1,3}']);
Route::rule('index','index/Index/index','get',['ext'=>'html|htm']);
Route::rule('flow1','index/Flow/flow1','get',['ext'=>'html|htm']);
// Route::rule('flow2','index/Flow/flow2','post',['ext'=>'html|htm']);
// Route::rule('category/:id','index/Category/index','get',['ext'=>'html|htm'],['id'=>'\d{1,3}']);
Route::rule('goods/:id','index/Goods/index','get',['ext'=>'html|htm'],['id'=>'\d{1,3}']);
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
