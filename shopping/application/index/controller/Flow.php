<?php
namespace app\index\controller;
use think\Cache;
class Flow extends Base
{
  public function addToCart(){
    //  1-3,4,5=>4
    $data=input('post.');
    $goodsObj=json_decode($data['goods']);
    $goodsId=$goodsObj->goods_id;
    $goodsAttr=$goodsObj->goods_attr_ids;    //string '72,75'
    $goodsNum=$goodsObj->number;
    // dump($goodsId);die;
    model('cart')->addToCart($goodsId,$goodsAttr,$goodsNum=1);
    $result=[
        'error'=>0,
        'one_step_buy'=>1,
    ];//error=0说明加入购物车成功，库存没问题   =2说明库存不足，未加入购物车
    return json($result);
  }
   //在购物车列表展示商品
  public function flow1(){
    $cartRes=model('cart')->getGoodsListInCart();
    $this->assign([
        'cartRes'=>$cartRes,
    ]);
    return view();
  }

  //购物结算页面
  public function flow2(){
    $doGoods=input('cart_value');
    $cartGoodsRes=model('cart')->getGoodsListInCart($doGoods);
    $uAdress = db('adress')->where('user_id',session('uid'))->find();
    $this->assign([
        'doGoodsRes'=>$cartGoodsRes,
        'uAdress'=>$uAdress,
        'doGoods'=>$doGoods,
    ]);
    return view();
  }

  //提交表单写入数据
  public function flow3(){
    $uid = session('uid');
    $doGoods=input('cart_value'); //选中的购物车中的商品
    //判断用户是否已经登录
    //统计用户收货信息
    $adress=db('adress');
    $adData['name']=input('name');
    $adData['phone']=input('phone');
    $adData['tel']=input('tel');
    $adData['province']=input('province');
    $adData['city']=input('city');
    $adData['county']=input('county');
    $adData['address']=input('address');
    $adData['email']=input('email');
    $adData['zipcode']=input('zipcode');
    $adData['sign_building']=input('sign_building');
    $adData['best_time']=input('best_time');
    $adData['user_id']=$uid;
    //处理用户收货地址信息  如果是第一次下单，就添加收货地址 否则，就修改地址
    $uAdress = $adress->where('user_id',$uid)->find();
    if($uAdress){
      $adress->where('user_id',$uid)->update($adData);
    }else{
      $adress->insert($adData);
    }
    //处理订单基本信息表
    $orderData['out_trade_no']=time().rand(111111,999999);
    $orderData['user_id']=$uid;
    $orderData['goods_total_price']=model('cart')->doGoodsPriceCount($doGoods);//选中的购物车中的商品总价
    $orderData['post_spent']=10;//运费
    $orderData['order_total_price']=($orderData['goods_total_price']+$orderData['post_spent']);//实际要支付的订单总价
    $orderData['payment']=input('payment');
    $orderData['distribution']=input('distribution');
    $orderData['name']=input('name');
    $orderData['phone']=input('phone');
    $orderData['province']=input('province');
    $orderData['city']=input('city');
    $orderData['county']=input('county');
    $orderData['address']=input('address');
    $orderData['order_time']=time();
    $orderId=db('order')->insertGETId($orderData);
    //处理订单中商品的基本信息
    if($orderId){
         
      $cartGoodsRes=model('cart')->getGoodsListInCart($doGoods);
      foreach ($cartGoodsRes as $k => $v) {
        $ogData['goods_id']=$v['goods_id'];
        $ogData['goods_name']=$v['goods_name'];
        $ogData['member_price']=$v['member_price'];
        $ogData['shop_price']=$v['shop_price'];
        $ogData['market_price']=$v['markte_price'];
        $ogData['goods_attr_id']=$v['goods_id_attr_id'];//含商品ID
        $ogData['goods_attr_str']=$v['goods_attr_str'];
        $ogData['goods_num']=$v['goods_num'];
        $ogData['order_id']=$orderId;
        db('order_goods')->insert($ogData);
        $arr=explode('-', $ogData['goods_attr_id']);
        $result=db('product')->field('p.*,g.xl')->alias('p')->join('goods g','p.goods_id=g.id')->where('goods_id',$arr[0])->select();
        if($result){
          foreach ($result as $k => $v) {
            $arr1=explode(',',$v['goods_attr']);
            $arr2=explode(',',$arr[1]);
            if(in_array($arr2[0],$arr1)&&in_array($arr2[1],$arr1)){
              $v['goods_number']=$v['goods_number']-$ogData['goods_num'];
              $v['xl']=$v['xl']+$ogData['goods_num'];
              if($v['goods_number']>=0){
                db('product')->where('id',$v['id'])->update(['goods_number'=>$v['goods_number']]);
                db('goods')->where('id',$ogData['goods_id'])->update(['xl'=>$v['xl']]);
              }else{
                $this->error('您购物车中的商品当前无库存，请选择其他商品下单，给您带来不便，请谅解！',url('index/Flow/flow1'));
              }
            }
          }
        }
      }
      
      cookie('cart',NULL);
      $this->success('下单成功！',url('index/Flow/flow4',['oid'=>$orderId],''));

    }
   
    // dump($_POST);
  }

  public function flow4(){
    $orderId=input('oid');
    $orderInfo=db('order')->field('id,out_trade_no,order_total_price,payment,distribution,address,province,city,county,phone,name')->find($orderId);
    $this->assign([
      'orderInfo'=>$orderInfo,
    ]);
    return view();
  }

  //微信支付二维码生成
  public function wxewm($outTradeNo){
    $orderTotalPrice = db('order')->where('out_trade_no',$outTradeNo)->value('order_total_price');
        $orderTotalPrice = $orderTotalPrice*100;
        $payPlus = PAY_PLUS.'./pay/wxpay/';
        include($payPlus.'index2.php');
        $obj = new WeiXinPay2();
        $qrurl = $obj->getQrUrl($outTradeNo,$orderTotalPrice);
         //生成二维码
         \QRcode::png($qrurl);
  }

  public function ajaxCartGoodsAmount(){
    $recId=input('rec_id');
    $result=model('cart')->ajaxCartGoodsAmount($recId);
    return json($result);
  }

  //删除购物车记录
  public function dropGoods(){
    $idAttr=input('id_attr');
    model('Cart')->delCart($idAttr);
    $this->redirect('index/Flow/flow1');
  }

  //批量删除购物车记录
  public function deleteCartGoods(){
    $cartValue=input('cart_value');  //要批量删除的购物车记录,格式：cart_value: 32-86,89@32-87,89
    model('Cart')->deleteCartGoods($cartValue);
    return json(['status'=>1]); //删除成功
  }

  //修改购物车商品数量
  public function updateCart(){
    $goodsNum=input('goods_number');
    $idAttr=input('rec_id');
    return model('Cart')->updateCart($idAttr,$goodsNum);
  }

  public function loginDailog(){
    $backUrl=input('back_act','');
    $ajaxLoginUrl=url('member/Account/login');
      $content="<div class=\"login-wrap\">\n    \n    <div class=\"login-form\">\n    \t    \t<div class=\"coagent\">\n            <div class=\"tit\"><h3>用第三方账号直接登录<\/h3><span><\/span><\/div>\n            <div class=\"coagent-warp\">\n            \t                                    <a href=\"user.php?act=oath&type=qq&user_callblock=flow.php\" class=\"qq\"><b class=\"third-party-icon qq-icon\"><\/b><\/a>\n                                            <\/div>\n        <\/div>\n                <div class=\"login-box\">\n            <div class=\"tit\"><h3>账号登录<\/h3><span><\/span><\/div>\n            <div class=\"msg-wrap\"><\/div>\n            <div class=\"form\">\n            \t<form name=\"formLogin\" action=\"user.php\" method=\"post\" onSubmit=\"userLogin();return false;\">\n                \t<div class=\"item\">\n                        <div class=\"item-info\">\n                            <i class=\"iconfont icon-name\"><\/i>\n                            <input type=\"text\" id=\"loginname\" name=\"username\" class=\"text\" value=\"\" placeholder=\"\u7528\u6237\u540d\/\u90ae\u7bb1\/\u624b\u673a\" \/>\n                        <\/div>\n                    <\/div>\n                    <div class=\"item\">\n                        <div class=\"item-info\">\n                            <i class=\"iconfont icon-password\"><\/i>\n                            <input type=\"password\" style=\"display:none\"\/>\n                            <input type=\"password\" id=\"nloginpwd\" name=\"password\" value=\"\" class=\"text\" placeholder=\"\u5bc6\u7801\" \/>\n                        <\/div>\n                    <\/div>\n                                        <div class=\"item\">\n                        <input id=\"remember\" name=\"remember\" type=\"checkbox\" class=\"ui-checkbox\">\n                        <label for=\"remember\" class=\"ui-label\">请保存我这次的登录信息。<\/label>\n                    <\/div>\n                    <div class=\"item item-button\">\n                    \t<input type=\"hidden\" name=\"dsc_token\" value=\"21b3d54ba27621d60137691f1a1c4518\" \/>\n                        <input type=\"hidden\" name=\"act\" value=\"act_login\" \/>\n                        <input type=\"hidden\" name=\"back_act\" value=\"".$backUrl."\" \/>\n                        <input type=\"submit\" name=\"submit\" value=\"登录\" class=\"btn sc-redBg-btn\" \/>\n                    <\/div>\n                    <div class=\"lie\">\n                    \t<a href=\"user.php?act=get_password\" class=\"notpwd gary fl\" target=\"_blank\">忘记密码？<\/a>\n                    \t<a href=\"user.php?act=register\" class=\"notpwd red fr\" target=\"_blank\">免费注册<\/a>                    <\/div>\n                <\/form>\n            <\/div>\n    \t<\/div>        \n    <\/div>\n    <script type=\"text\/javascript\">\n\t\tvar username_empty=\"<i><\/i>\u8bf7\u8f93\u5165\u7528\u6237\u540d\";\n    \tvar username_shorter=\"<i><\/i>\u7528\u6237\u540d\u957f\u5ea6\u4e0d\u80fd\u5c11\u4e8e 4 \u4e2a\u5b57\u7b26\u3002\";\n    \tvar username_invalid=\"<i><\/i>\u7528\u6237\u540d\u53ea\u80fd\u662f\u7531\u5b57\u6bcd\u6570\u5b57\u4ee5\u53ca\u4e0b\u5212\u7ebf\u7ec4\u6210\u3002\";\n    \tvar password_empty=\"<i><\/i>\u8bf7\u8f93\u5165\u5bc6\u7801\";\n    \tvar password_shorter=\"<i><\/i>\u767b\u5f55\u5bc6\u7801\u4e0d\u80fd\u5c11\u4e8e 6 \u4e2a\u5b57\u7b26\u3002\";\n    \tvar confirm_password_invalid=\"<i><\/i>\u4e24\u6b21\u8f93\u5165\u5bc6\u7801\u4e0d\u4e00\u81f4\";\n    \tvar captcha_empty=\"<i><\/i>\u8bf7\u8f93\u5165\u9a8c\u8bc1\u7801\";\n    \tvar email_empty=\"<i><\/i>Email \u4e3a\u7a7a\";\n    \tvar email_invalid=\"<i><\/i>Email \u4e0d\u662f\u5408\u6cd5\u7684\u5730\u5740\";\n    \tvar agreement=\"<i><\/i>\u60a8\u6ca1\u6709\u63a5\u53d7\u534f\u8bae\";\n    \tvar msn_invalid=\"<i><\/i>msn\u5730\u5740\u4e0d\u662f\u4e00\u4e2a\u6709\u6548\u7684\u90ae\u4ef6\u5730\u5740\";\n    \tvar qq_invalid=\"<i><\/i>QQ\u53f7\u7801\u4e0d\u662f\u4e00\u4e2a\u6709\u6548\u7684\u53f7\u7801\";\n    \tvar home_phone_invalid=\"<i><\/i>\u5bb6\u5ead\u7535\u8bdd\u4e0d\u662f\u4e00\u4e2a\u6709\u6548\u53f7\u7801\";\n    \tvar office_phone_invalid=\"<i><\/i>\u529e\u516c\u7535\u8bdd\u4e0d\u662f\u4e00\u4e2a\u6709\u6548\u53f7\u7801\";\n    \tvar mobile_phone_invalid=\"<i><\/i>\u624b\u673a\u53f7\u7801\u4e0d\u662f\u4e00\u4e2a\u6709\u6548\u53f7\u7801\";\n    \tvar msg_un_blank=\"<i><\/i>\u7528\u6237\u540d\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar msg_un_length=\"<i><\/i>\u7528\u6237\u540d\u6700\u957f\u4e0d\u5f97\u8d85\u8fc715\u4e2a\u5b57\u7b26\uff0c\u4e00\u4e2a\u6c49\u5b57\u7b49\u4e8e2\u4e2a\u5b57\u7b26\";\n    \tvar msg_un_format=\"<i><\/i>\u7528\u6237\u540d\u542b\u6709\u975e\u6cd5\u5b57\u7b26\";\n    \tvar msg_un_registered=\"<i><\/i>\u7528\u6237\u540d\u5df2\u7ecf\u5b58\u5728,\u8bf7\u91cd\u65b0\u8f93\u5165\";\n    \tvar msg_can_rg=\"<i><\/i>\u53ef\u4ee5\u6ce8\u518c\";\n    \tvar msg_email_blank=\"<i><\/i>\u90ae\u4ef6\u5730\u5740\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar msg_email_registered=\"<i><\/i>\u90ae\u7bb1\u5df2\u5b58\u5728,\u8bf7\u91cd\u65b0\u8f93\u5165\";\n    \tvar msg_email_format=\"<i><\/i>\u683c\u5f0f\u9519\u8bef\uff0c\u8bf7\u8f93\u5165\u6b63\u786e\u7684\u90ae\u7bb1\u5730\u5740\";\n    \tvar msg_blank=\"<i><\/i>\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar no_select_question=\"<i><\/i>\u60a8\u6ca1\u6709\u5b8c\u6210\u5bc6\u7801\u63d0\u793a\u95ee\u9898\u7684\u64cd\u4f5c\";\n    \tvar passwd_balnk=\"<i><\/i>\u5bc6\u7801\u4e2d\u4e0d\u80fd\u5305\u542b\u7a7a\u683c\";\n    \tvar msg_phone_blank=\"<i><\/i>\u624b\u673a\u53f7\u7801\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar msg_phone_registered=\"<i><\/i>\u624b\u673a\u5df2\u5b58\u5728,\u8bf7\u91cd\u65b0\u8f93\u5165\";\n    \tvar msg_phone_invalid=\"<i><\/i>\u65e0\u6548\u7684\u624b\u673a\u53f7\u7801\";\n    \tvar msg_phone_not_correct=\"<i><\/i>\u624b\u673a\u53f7\u7801\u4e0d\u6b63\u786e\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\";\n    \tvar msg_mobile_code_blank=\"<i><\/i>\u624b\u673a\u9a8c\u8bc1\u7801\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar msg_mobile_code_not_correct=\"<i><\/i>\u624b\u673a\u9a8c\u8bc1\u7801\u4e0d\u6b63\u786e\";\n    \tvar msg_confirm_pwd_blank=\"<i><\/i>\u786e\u8ba4\u5bc6\u7801\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar msg_identifying_code=\"<i><\/i>\u9a8c\u8bc1\u7801\u4e0d\u80fd\u4e3a\u7a7a\";\n    \tvar msg_identifying_not_correct=\"<i><\/i>\u9a8c\u8bc1\u7801\u4e0d\u6b63\u786e\";\n    \t\t\/* *\n\t\t * \u4f1a\u5458\u767b\u5f55\n\t\t*\/ \n\t\tfunction userLogin()\n\t\t{\n\t\t\tvar frm = $(\"form[name='formLogin']\");\n\t\t\tvar username = frm.find(\"input[name='username']\");\n\t\t\tvar password = frm.find(\"input[name='password']\");\n\t\t\tvar captcha = frm.find(\"input[name='captcha']\");\n\t\t\tvar dsc_token = frm.find(\"input[name='dsc_token']\");\n\t\t\tvar error = frm.find(\".msg-error\");\n\t\t\tvar msg = '';\n\t\t\t\n\t\t\tif(username.val()==\"\"){\n\t\t\t\terror.show();\n\t\t\t\tusername.parents(\".item\").addClass(\"item-error\");\n\t\t\t\tmsg += username_empty;\n\t\t\t\tshowMesInfo(msg);\n\t\t\t\treturn false;\n\t\t\t}\n\t\t\t\n\t\t\tif(password.val()==\"\"){\n\t\t\t\terror.show();\n\t\t\t\tpassword.parents(\".item\").addClass(\"item-error\");\n\t\t\t\tmsg += password_empty;\n\t\t\t\tshowMesInfo(msg);\n\t\t\t\treturn false;\n\t\t\t}\n\t\t\t\n\t\t\tif(captcha.val()==\"\"){\n\t\t\t\terror.show();\n\t\t\t\tcaptcha.parents(\".item\").addClass(\"item-error\");\n\t\t\t\tmsg += captcha_empty;\n\t\t\t\tshowMesInfo(msg);\n\t\t\t\treturn false;\n\t\t\t}\n\t\t\tvar back_act=frm.find(\"input[name='back_act']\").val();\n\t\t\t\n\t\t\t\t\t\t\tAjax.call( '".$ajaxLoginUrl."', 'username=' + username.val()+'&password='+password.val()+'&dsc_token='+dsc_token.val()+'&captcha='+captcha.val()+'&back_act='+back_act, return_login , 'POST', 'JSON');\n\t\t\t\t\t}\n\t\t\n\t\tfunction return_login(result)\n\t\t{\n\t\t\tif(result.error>0)\n\t\t\t{\n\t\t\t\tshowMesInfo(result.message);\t\n\t\t\t}\n\t\t\telse\n\t\t\t{\n\t\t\t\tif(result.ucdata){\n\t\t\t\t\t$(\"body\").append(result.ucdata)\n\t\t\t\t}\n\t\t\t\tlocation.href='http://www.shopping.com:9001/index.php/flow1.html';\n\t\t\t}\n\t\t}\n\t\t\n\t\tfunction showMesInfo(msg) {\n\t\t\t$('.login-wrap .msg-wrap').empty();\n\t\t\tvar info = '<div class=\"msg-error\"><b><\/b>' + msg + '<\/div>';\n\t\t\t$('.login-wrap .msg-wrap').append(info);\n\t\t}\n\t<\/script>\n<\/div>\n";
      $content=stripcslashes($content);
      return json(["error"=>0,"message"=>"","content"=>$content]);
  }


  public function cartGoodsNum(){
    $cart=isset($_COOKIE['cart']) ? unserialize($_COOKIE['cart']) : [];
    $cartGoodsNum=0;
    foreach ($cart as $k => $v) {
        $cartGoodsNum+=$v;
    }
    return json(['cart_goods_num'=>$cartGoodsNum]);
  }

}
