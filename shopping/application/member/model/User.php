<?php
namespace app\member\model;
use think\Model;
class User extends Model
{
  public function login($data,$type=0,$backAct='#'){
    $password=md5($data['password']);
    $loginRes=$this->where(['username'=>$data['username']])->whereOr(['email'=>$data['username']])->whereOr(['mobile_phone'=>$data['username']])->find();
            if($loginRes){
                if($password==$loginRes['password']){
                  session('uid',$loginRes['id']);
                  session('uname',$loginRes['username']);
                  //写入会员等级及折扣率
                  $points=$loginRes['point'];
                  // dump($points);die;
                  $memberLevel=db('member_level')->where('bom_point','<=',$points)->where('top_point','>=',$points)->find();
                  session('level_id',$memberLevel['id']);
                  session('level_rate',$memberLevel['rate']);
                  session('point',$points);
                  if(isset($data['remember'])){
                    $t=30*24*60*60;
                    $uname=encryption($loginRes['username'],0);
                    $upassword=encryption($data['password'],0);
                    cookie('uname',$uname,$t,'/');
                    cookie('upassword',$upassword,$t,'/');
                  }
                  $arr=[
                        'error'=>0,
                        'message'=>"",
                        'url'=>$backAct
                    ];
                    if($type == 1){
                        return $arr;
                    }else{
                        return json($arr); 
                    }
                }else{
                    $arr=[
                        'error'=>1,
                        'message'=>"<i class='iconfont icon-minus-sign'></i>用户名或者密码错误~",
                        'url'=>'',
                    ];
                    if($type == 1){
                        return $arr;
                    }else{
                        return json($arr); 
                    }
                }
            }else{
                $arr=[
                    'error'=>1,
                    'message'=>"<i class='iconfont icon-minus-sign'></i>用户名或者密码错误~",
                    'url'=>'',
                ];
                if($type == 1){
                        return $arr;
                    }else{
                        return json($arr); 
                    }
            }
  }

}
