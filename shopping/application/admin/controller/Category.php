<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class Category extends Controller
{
    public function lst()
    {
        $category=new Catetree();
        $categoryObj=db('category');
        if(request()->isPost()){
            $data=input('post.');
            $category->cateSort($data['sort'],$categoryObj);
            $this->success('排序成功',url('lst'));
        }
        $categoryRes=$categoryObj->order('sort DESC')->select();
        $categoryRes=$category->catetree($categoryRes);
        $this->assign([
        'categoryRes'=>$categoryRes,
        ]);
        return view('list');
    }

    public function add()
    {
        $categoryObj=model('category');
        if(request()->isPost()){
            $data=input('post.');
            //验证
            // $validate = validate('category');
            // if (!$validate->check($data)) {
            //     $this->error($validate->getError());
            // }
            if($_FILES['cate_img']['tmp_name']){
                $data['cate_img']=$this->upload();
            }
            $add=$categoryObj->save($data);
            if($add){
             $this->success('添加分类成功！','lst'); 
         }else{
            $this->error('添加分类失败！');
        }
        return;
    }
    //商品分类推荐位
    $categoryRecposRes=db('recpos')->where('rec_type',2)->select();
    $categoryRes=$categoryObj->order('sort DESC')->select();
    $category=new Catetree();
    $categoryRes=$category->catetree($categoryRes);
    $this->assign([
        'categoryRes'=>$categoryRes,
        'categoryRecposRes'=>$categoryRecposRes,
    ]);
    return view();
}

public function edit()
{
    $category=new Catetree();
    $categoryObj=model('category');
    if(request()->isPost()){
        $data=input('post.');
        //处理图片上传
        if($_FILES['cate_img']['tmp_name']){
            $data['cate_img']=$this->upload();
            $categorys=$categoryObj->field('cate_img')->find($data['id']);
            if($categorys['cate_img']){
                $imgSrc=IMG_UPLOADS.$categorys['cate_img'];
                if(file_exists($imgSrc)){
                    @unlink($imgSrc);
                }
            }
        }    
         //   验证
        // $validate = validate('category');
        // if (!$validate->check($data)) {
        //     $this->error($validate->getError());
        // }
        $save=$categoryObj->update($data);
        if($save !== false){
           $this->success('修改分类成功！','lst'); 
       }else{
        $this->error('修改分类失败！');
    }
    return;
}

    //商品分类推荐位
    $categoryRecposRes=db('recpos')->where('rec_type',2)->select();
    //当前商品分类相关推荐位
    $_myCategoryRecposRes=db('rec_item')->where(['value_type'=>2,'value_id'=>input('id')])->select();
    $myCategoryRecposRes=[];
    foreach ($_myCategoryRecposRes as $k => $v) {
        $myCategoryRecposRes[]=$v['recpos_id'];
    }
$categorys=$categoryObj->find(input('id'));
$categoryRes=$categoryObj->order('sort DESC')->select();
$categoryRes=$category->catetree($categoryRes);
$this->assign([
    'categoryRes'=>$categoryRes,
    'categorys'=>$categorys,
    'categoryRecposRes'=>$categoryRecposRes,
    'myCategoryRecposRes'=>$myCategoryRecposRes,
]);
return view();
}

public function del($id)
{
    $category=db('category');
    $categoryTree=new Catetree();
    $sonids=$categoryTree->childrenids($id,$category);
    $sonids[]=intval($id);
    //删除分类前判断该分类下的文章和文章相关缩略图
    // $article=db('article');
    // foreach ($sonids as $k => $v) {
    //     $artRes=$article->field('id,thumb')->where('category_id',$v)->select();
    //     foreach ($artRes as $ke => $ve) {
    //         $thumbSrc=IMG_UPLOADS.$ve['thumb'];
    //         if(file_exists($thumbSrc)){
    //             @unlink($thumbSrc);
    //         }
    //         $article->delete($ve['id']);
    //     }
    // }
    // 删除栏目钱检查并删除当前栏目的推荐信息
    $recItem=db('recItem');
    foreach ($sonids as $k => $v) {
        $recItem->where(['value_id'=>$v,'value_type'=>2])->delete();
    }
    $del=$category->delete($sonids);
    if($del){
       $this->success('删除分类成功！','lst'); 
   }else{
    $this->error('删除分类失败！');
}
}

    //上传图片的操作类
public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('cate_img');
    
    // 移动到框架应用根目录/public/uploads/ 目录下
    if($file){
        $info = $file->move(ROOT_PATH . 'public' . DS .'static' . DS . 'uploads');
        if($info){
            return $info->getSaveName();
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
            die;
        }
    }
}
}
