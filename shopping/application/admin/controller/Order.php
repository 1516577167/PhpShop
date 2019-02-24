<?php
namespace app\admin\controller;
use think\Controller;
class Order extends Controller
{
    public function lst()
    {
        if(request()->isPost()){
            $data=input('post.');
            $where=[];  
            $selectValue=trim($data['select_value']);
            if($data['select_base']=='order_trade_no'){
                $where['o.out_trade_no']=['=',$selectValue];
            }else{
                $userId=db('user')->where('username',$selectValue)->value('id');
                $where['o.user_id']=['=',$userId];
            }
        }
        if(!isset($where)){
                $where=1;
            }
        $orderRes=db('order')->alias('o')->join("user u",'o.user_id=u.id')->field('o.*,u.username')->where($where)->order('o.id desc')->paginate(10);
        $this->assign([
            'orderRes'=>$orderRes,
        ]);
        return view('list');
    }

    //订单查询
    public function orderSelect(){
        return view();
    }

   public function detail($id){
        $orderInfo=db('order')->alias('o')->join("user u",'o.user_id=u.id')->field('o.*,u.username')->find($id);
        $this->assign([
            'orderInfo'=>$orderInfo,
        ]);
        return view('detail');
   }

   //删除订单
   public function del($id){
        //删除订单商品表的关联数据
        db('order_goods')->where('order_id',$id)->delete();
        $del=db('order')->delete($id);
        if($del){
            $this->success('订单删除成功！');
        }else{
            $this->error('订单删除失败！');
        }
   }

public function edit()
{
    if(request()->isPost()){
        $data=input('post.');
        $userId=db('user')->where('username',$data['username'])->value('id');
        if($userId){
            $data['user_id']=$userId;
        }
        $data['order_time']=strtotime($data['order_time']);

        // dump($data); die;
            //验证
        // $validate = validate('order');
        // if (!$validate->check($data)) {
        //     $this->error($validate->getError());
        // }
        $save=db('order')->strict(false)->update($data);
        if($save !== false){
         $this->success('修改订单成功！','lst'); 
     }else{
        $this->error('修改订单失败！');
    }
    return;
}
$id=input('id');
$orderInfo=db('order')->alias('o')->join("user u",'o.user_id=u.id')->field('o.*,u.username')->find($id);
$this->assign([
    'orderInfo'=>$orderInfo,
]);
return view();
}

    public function orderGoods($id){
        $orderGoods=db('orderGoods')->where('order_id',$id)->paginate(10);
        $this->assign([
            'orderGoods'=>$orderGoods,
        ]);
        return view();
    }

    public function orderGoodsEdit(){
        if(request()->isPost()){
            $data=input('post.');
            $save=db('order_goods')->update($data);
            if($save!=false){
                $this->success('修改订单商品成功！');
            }else{
                $this->error('修改订单商品失败！');
            }
        }
        $orderGoodsId=input('id');
        $orderGoodsInfo=db('orderGoods')->find($orderGoodsId);
        $this->assign([
            'orderGoodsInfo'=>$orderGoodsInfo,
        ]);
        return view();
    }

    public function orderGoodsDel($id){
        $res=db('orderGoods')->delete($id);
        $this->success('删除订单商品成功！');
    }
}
