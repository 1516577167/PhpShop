<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree;
class Article extends Controller
{
    public function lst()
    {
        $articleRes=db('article')->field('a.*,c.cate_name')->alias('a')->join('cate c',"a.cate_id=c.id")->order('id desc')->paginate(10);
        $this->assign([
            'artRes'=>$articleRes,
        ]);
        return view('list');
    }

    public function add()
    {
        $cate=new Catetree();
        if(request()->isPost()){
            $data=input('post.');
            $data['addtime']=time();
            //strpos区别字母大小写，判断某段字符在数据中是否存在，存在返回第一个字母的位置，不存在返回FALSE；stripos不区别大小写，比如HTTP://
            if($data['link_url']&&stripos($data['link_url'], 'http://') === false){
                $data['link_url']='http://'.$data['link_url'];
            }
            //处理图片上传
            if($_FILES['thumb']['tmp_name']){
                $data['thumb']=$this->upload();
            }
            //验证
            $validate = validate('Article');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $add=db('article')->insert($data);
            if($add){
             $this->success('添加文章成功！','lst'); 
         }else{
            $this->error('添加文章失败！');
        }
        return;
    }
    $cateRes=db('cate')->order('sort DESC')->select();
        $cateRes=$cate->catetree($cateRes);
        $this->assign([
        'cateRes'=>$cateRes,
        ]);
    return view();
}

public function edit()
{
    $cate=new Catetree();
    if(request()->isPost()){
        $data=input('post.');
            //strpos区别字母大小写，判断某段字符在数据中是否存在，存在返回第一个字母的位置，不存在返回FALSE；stripos不区别大小写，比如HTTP://
        if($data['link_url']&&stripos($data['link_url'], 'http://') === false){
            $data['link_url']='http://'.$data['link_url'];
        }
            //处理图片上传
        if($_FILES['thumb']['tmp_name']){
            $oldarticles=db('article')->field('thumb')->find($data['id']);
            $oldarticleImg=IMG_UPLOADS.$oldarticles['thumb'];
            if(file_exists($oldarticleImg)){
                @unlink($oldarticleImg);
            }
            $data['thumb']=$this->upload();
        }
            //验证
        $validate = validate('article');
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $save=db('article')->update($data);
        if($save !== false){
         $this->success('修改文章成功！','lst'); 
     }else{
        $this->error('修改文章失败！');
    }
    return;
}
        $id=input('id');
        $arts=db('article')->find($id);
        $cateRes=db('cate')->order('sort DESC')->select();
        $cateRes=$cate->catetree($cateRes);
        $this->assign([
            'arts'=>$arts,
            'cateRes'=>$cateRes,
        ]);
        return view();
}

public function del($id)
{
    $article=db('article');
    $arts=$article->where('id',$id)->value('thumb');
    $thumbSrc=IMG_UPLOADS.$arts;
    $del=$article->delete($id);
    if($del){
    if(file_exists($thumbSrc)){
                @unlink($thumbSrc);
            }
     $this->success('删除文章成功！','lst'); 
 }else{
    $this->error('删除文章失败！');
}
}

    //上传图片的操作类
public function upload(){
            // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('thumb');
        
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

    //ueditor图片管理
    public function imglist(){
        $_files=my_scandir();
        $files=[];
        foreach ($_files as $k => $v) {
            if(is_array($v)){
                foreach ($v as $k1 => $v1) {
                    $v1=str_replace(UEDITOR, HTTP_UEDITOR,$v1);
                    $files[]=$v1;
                }
            }else{
                $v=str_replace(UEDITOR, HTTP_UEDITOR,$v);
                $files[]=$v;
            }
        }
        // dump($files); die;
        $this->assign('imgRes',$files);
        return view();
    }

    public function delimg(){
        
    }
}
