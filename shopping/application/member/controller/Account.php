<?php
namespace app\member\controller;
use ChuanglanSmsHelper\ChuanglanSmsApi;
use PHPMailer\PHPMailer\PHPMailer;
use app\index\controller\Base;
class Account extends Base
{
    public function reg(){
        if(request()->isPost()){
            $data=input('post.');
            // $validate = validate('User');
            // if(!$validate->check($data)){
            //     dump($validate->getError());
            // }
            $userData=[];
            $userData['username']=trim($data['username']);
            $userData['password']=md5($data['password']);
            $userData['mobile_phone']=$data['mobile_phone'];
            $userData['email']=$data['email'];
            if($data['send_code']==session('emailCode')){
                $userData['checked']=1;
            }else{
                $this->error('邮箱验证码错误，请重试~');
            }
            $userData['register_time']=time();
            $add=db('user')->insert($userData);
            if($add){
                $res=$this->login(1);
                if($res['error']==0){
                    $this->success('注册成功，请等待跳转~~','member/User/index');
                }else{
                    $this->success('注册成功，请等待跳转~~','member/Account/login');
                }
            }else{
                $this->error('注册失败，请重试~');
            }
        }
        return view();
    }
//type=0 说明需要为客户端返回JSON对象数组，type=1说明要为服务端返回普通数组类型
    public function login($type=0){
        if(request()->isPost()){
            $data=[];
            $data['username']=trim(input('username'));
            $data['password']=input('password');
            $data['remember']=input('remember');
            $backAct=input('back_act');
            // dump($backAct);die;
            return model('user')->login($data,$type,$backAct='#');
        }
        return view();
    }

    public function sendMsg(){
        $clapi= new ChuanglanSmsApi();
        $code= mt_rand(100000,999999);
        $result= $clapi->sendSMS(input('phoneNum'), '您好，您的验证码是'. $code);

        if(!is_null(json_decode($result))){
        $output=json_decode($result,true);
        if(isset($output['code'])  && $output['code']=='0'){
            echo '短信发送成功！';
        }else{    
            echo $output['errorMsg'];
            }
        }else{    
            echo $result;
        }
    }

    public function sendmail($email='',$password=''){
        if($email){
            $to=$email;
        }else{
            $to=input('email');
        }
        $title='嗨购-方便生活的购物网站 欢迎您的注册~~';
        $code= mt_rand(100000,999999);
        $content='';
        if($password){
            $content='您的新密码是'.$password.',请妥善保管~';
        }else{
            $content='欢迎您的注册，您的验证码:'.$code;
        }
    // require_once('class.phpmailer.php');
    $mail = new PHPMailer();
    // 设置为要发邮件
    $mail->IsSMTP();
    // 是否允许发送HTML代码做为邮件的内容
    $mail->IsHTML(TRUE);
    $mail->CharSet='UTF-8';
    // 是否需要身份验证
    $mail->SMTPAuth=TRUE;
    /*  邮件服务器上的账号是什么 -> 到163注册一个账号即可 */
    $mail->From="18850589041@163.com";
    $mail->FromName="嗨购商城";
    $mail->Host="smtp.163.com";  //发送邮件的服务协议地址
    $mail->Username="18850589041@163.com";
    $mail->Password="que350822";
    // 发邮件端口号默认25
    $mail->Port = 25;
    // 收件人
    $mail->AddAddress($to);
    // 邮件标题 
    $mail->Subject=$title;
    // 邮件内容
    $mail->Body=$content;
    $sendRes=$mail->Send();
    if($sendRes){
        //记录邮件验证码
        session('emailCode',$code);
        $msg=['status'=>0,'msg'=>'发送成功'];
        return json($msg);
    }else{
        $msg=['status'=>1,'msg'=>'发送失败'];
        return json($msg);
    }
    }

    public function isRegistered(){
        if(request()->isAjax()){
            $username=input("username");
            $userRes=db('user')->where(['username'=>$username])->find();
            if($userRes){
                return false;
            }else{
                return true;
            }
        }else{
            $this->error('非法请求');
        }
    }

    public function checkPhone(){
        if(request()->isAjax()){
            $mobilePhone=input("mobile_phone");
            $mobileRes=db('user')->where(['mobile_phone'=>$mobilePhone])->find();
            if($mobileRes){
                return false;
            }else{
                return true;
            }
        }else{
            $this->error('非法请求');
        }
    }

    public function checkEmail(){
        if(request()->isAjax()){
            $email=input("email");
            $emailRes=db('user')->where(['email'=>$email])->find();
            if($emailRes){
                return false;
            }else{
                return true;
            }
        }else{
            $this->error('非法请求');
        }
    }


    //验证邮箱验证码
    public function checkdEmailSendCode(){
        if(request()->isAjax()){
            $emailCode=input("send_code");
            if($emailCode == session('emailCode')){
                return true;
            }else{
                return false;
            }
        }else{
            $this->error('非法请求');
        }
    }

        public function checkLogin(){
        $uid=session('uid');
        if($uid){
            $arr['error']=0;
            $arr['uname']=session('uname');
            $arr['uid']=$uid;
            return json($arr);
            }else{
                if(cookie('uname') && cookie('upassword')){
                    $data['username']=encryption(cookie('uname'),1);
                    $data['password']=encryption(cookie('upassword'),1);
                    $loginRes=model('user')->login($data,1);
                    if($loginRes['error'] == 0){
                        $arr['error']=0;
                        $arr['uname']=session('uname');
                        $arr['uid']=$uid;
                        return json($arr);
                    }
                }
                $arr['error']=1;
                return json($arr);
                }
            }

    public function getPassword(){
        return view();
    }


    //通过用户名和邮箱找回密码
    public function sendPwdEmail(){
        $data=input('post.');
        $userData['user_name']=trim($data['user_name']);
        $userData['email']=trim($data['email']);
        $users=db('user')->where(['username'=>$userData['user_name']])->find();
        if($users){
            if($users['email']==$userData['email']){
                $password=mt_rand(100000,999999);
                $_password=md5($password);
                $update=db('user')->where(['email'=>$userData['email']])->update(['password'=>$_password]);
                if($update){
                    $_msg=$this->sendmail($userData['email'],$password);
                    $msg['status']=0;
                    $msg['msg']='修改密码成功！';
                }else{
                    $msg['status']=3;
                    $msg['msg']='修改密码失败！';
                }
            }else{
                $msg['status']=2;
                $msg['msg']='您填写的电子邮箱错误，请重新输入';
            }
        }else{
            $msg['status']=1;
            $msg['msg']='您填写的用户名不存在，请重新输入';
        }
        $this->assign([
            'status'=>$msg['status'],
            'msg'=>$msg['msg'],
        ]);
        return view('index@common/tip_info');
    }

}
