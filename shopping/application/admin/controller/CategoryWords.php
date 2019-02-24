<?php
namespace app\admin\controller;
use think\Controller;
class CategoryWords extends Controller
{
    public function lst()
    {
        $cwRes=db('categoryWords')->field('cw.*,c.cate_name')->alias('cw')->join('category c',"cw.category_id=c.id")->order('id DESC')->select();
        $this->assign([
            'cwRes'=>$cwRes,
        ]);
        return view('list');
    }

    public function add()
    {
        if(request()->isPost()){
            $data=input('post.');
            //strpos区别字母大小写，判断某段字符在数据中是否存在，存在返回第一个字母的位置，不存在返回FALSE；stripos不区别大小写，比如HTTP://
            if($data['link_url']&&stripos($data['link_url'], 'http://') === false){
                $data['link_url']='http://'.$data['link_url'];
            }
            //验证
            $validate = validate('CategoryWords');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $add=db('categoryWords')->insert($data);
            if($add){
             $this->success('添加关联词成功！','lst'); 
         }else{
            $this->error('添加关联词失败！');
        }
        return;
    }
    $cateRes=model('Category')->where(['pid'=>0])->select();
    $this->assign([
        'cateRes'=>$cateRes,
    ]);
    return view();
}

public function edit()
{
    if(request()->isPost()){
        $data=input('post.');
            //strpos区别字母大小写，判断某段字符在数据中是否存在，存在返回第一个字母的位置，不存在返回FALSE；stripos不区别大小写，比如HTTP://
        if($data['link_url']&&stripos($data['link_url'], 'http://') === false){
            $data['link_url']='http://'.$data['link_url'];
        }
            //验证
        $validate = validate('CategoryWords');
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $save=db('categoryWords')->update($data);
        if($save !== false){
         $this->success('修改关联词成功！','lst'); 
     }else{
        $this->error('修改关联词失败！');
    }
    return;
}
$id=input('id');
$cws=db('categoryWords')->find($id);
$cateRes=model('Category')->where(['pid'=>0])->select();
$this->assign([
    'cws'=>$cws,
    'cateRes'=>$cateRes,
]);
return view();
}

public function del($id)
{
    $linkobj=db('categoryWords');
    $del=$linkobj->delete($id);
    if($del){
     $this->success('删除关联词成功！','lst'); 
 }else{
    $this->error('删除关联词失败！');
}
}

}
