<?php
namespace app\admin\controller;
use think\Controller;
class CategoryBrands extends Controller
{
    public function lst()
    {
        $cbRes=db('categoryBrands')->field('cb.*,c.cate_name,GROUP_CONCAT(b.brand_name) brand_name')->alias('cb')->join('category c',"cb.category_id=c.id")->join('brand b',"find_in_set(b.id,cb.brands_id)",'LEFT')->order('cb.id DESC')->group('cb.id')->paginate(6);
        $this->assign([
            'cbRes'=>$cbRes,
        ]);
        return view('list');
    }

    public function add()
    {
        if(request()->isPost()){
            $data=input('post.');
            //strpos区别字母大小写，判断某段字符在数据中是否存在，存在返回第一个字母的位置，不存在返回FALSE；stripos不区别大小写，比如HTTP://
            if($data['pro_url']&&stripos($data['pro_url'], 'http://') === false){
                $data['pro_url']='http://'.$data['pro_url'];
            }
            //处理图片上传
            if($_FILES['pro_img']['tmp_name']){
                $data['pro_img']=$this->upload();
            }
            if(isset($data['brands_id'])){
                $data['brands_id']=implode(',',$data['brands_id']);
            }
            //验证
            // $validate = validate('link');
            // if (!$validate->check($data)) {
            //     $this->error($validate->getError());
            // }
            $add=db('categoryBrands')->insert($data);
            if($add){
             $this->success('添加关联品牌成功！','lst'); 
         }else{
            $this->error('添加关联品牌失败！');
        }
        return;
    }
    //获取品牌信息,要求有图片的才显示
    $brandsRes=db('brand')->where('brand_img','NEQ','')->select();
    //获取所有的顶级分类
    $cateRes=model('Category')->where(['pid'=>0])->select();
    $this->assign([
        'cateRes'=>$cateRes,
        'brandsRes'=>$brandsRes,
    ]);
    return view();
}

public function edit()
    {
    //当前记录信息
    $categoryBrands=db('categoryBrands')->find(input('id'));
        if(request()->isPost()){
            $data=input('post.');
            //strpos区别字母大小写，判断某段字符在数据中是否存在，存在返回第一个字母的位置，不存在返回FALSE；stripos不区别大小写，比如HTTP://
            if($data['pro_url']&&stripos($data['pro_url'], 'http://') === false){
                $data['pro_url']='http://'.$data['pro_url'];
            }
            //处理图片上传
            if($_FILES['pro_img']['tmp_name']){
                //如果有原图 则进行删除
                if($categoryBrands['pro_img']){
                    $oldcbImg=IMG_UPLOADS.$categoryBrands['pro_img'];
                    if(file_exists($oldcbImg)){
                        @unlink($oldcbImg);
            }
        }
                $data['pro_img']=$this->upload();
            }
            if(isset($data['brands_id'])){
                $data['brands_id']=implode(',',$data['brands_id']);
            }else{
                $data['brands_id']='';
            }
            //验证
            // $validate = validate('link');
            // if (!$validate->check($data)) {
            //     $this->error($validate->getError());
            // }
            $add=db('categoryBrands')->update($data);
            if($add){
             $this->success('修改关联品牌成功！','lst'); 
            }else{
            $this->error('修改关联品牌失败！');
            }
            return;
        }
    //获取品牌信息,要求有图片的才显示
    $brandsRes=db('brand')->where('brand_img','NEQ','')->select();
    //获取所有的顶级分类
    $cateRes=model('Category')->where(['pid'=>0])->select();
    $this->assign([
        'cateRes'=>$cateRes,
        'brandsRes'=>$brandsRes,
        'categoryBrands'=>$categoryBrands,
    ]);
    return view();
}

public function del($id)
{
    $cbobj=db('categoryBrands');
    $cbs=$cbobj->field('pro_img')->find($id);
    if($cbs['pro_img']){
        $oldcbImg=IMG_UPLOADS.$cbs['pro_img'];
            if(file_exists($oldcbImg)){
                @unlink($oldcbImg);
            }
    }
    $del=$cbobj->delete($id);
    if($del){
     $this->success('删除关联品牌成功！','lst'); 
 }else{
    $this->error('删除关联品牌失败！');
}
}

    //上传图片的操作类
public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('pro_img');
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
