<?php
namespace app\admin\controller;
use think\Controller;
class Link extends Controller
{
    public function lst()
    {
        $linkRes=db('link')->order('id DESC')->paginate(6);
        $this->assign([
            'linkRes'=>$linkRes,
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
            //处理图片上传
            if($_FILES['logo']['tmp_name']){
                $data['logo']=$this->upload();
            }
            //验证
            // $validate = validate('link');
            // if (!$validate->check($data)) {
            //     $this->error($validate->getError());
            // }
            $add=db('link')->insert($data);
            if($add){
             $this->success('添加链接成功！','lst'); 
         }else{
            $this->error('添加链接失败！');
        }
        return;
    }
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
            //处理图片上传
        if($_FILES['logo']['tmp_name']){
            $oldlinks=db('link')->field('logo')->find($data['id']);
            $oldlinkImg=IMG_UPLOADS.$oldlinks['logo'];
            if(file_exists($oldlinkImg)){
                @unlink($oldlinkImg);
            }
            $data['logo']=$this->upload();
        }
            //验证
        // $validate = validate('link');
        // if (!$validate->check($data)) {
        //     $this->error($validate->getError());
        // }
        $save=db('link')->update($data);
        if($save !== false){
         $this->success('修改链接成功！','lst'); 
     }else{
        $this->error('修改链接失败！');
    }
    return;
}
$id=input('id');
$links=db('link')->find($id);
$this->assign([
    'links'=>$links,
]);
return view();
}

public function del($id)
{
    $linkobj=db('link');
    $links=$linkobj->field('logo')->find($id);
    if($links['logo']){
        $oldlinkImg=IMG_UPLOADS.$links['logo'];
            if(file_exists($oldlinkImg)){
                @unlink($oldlinkImg);
            }
    }
    $del=$linkobj->delete($id);
    if($del){
     $this->success('删除链接成功！','lst'); 
 }else{
    $this->error('删除链接失败！');
}
}

    //上传图片的操作类
public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('logo');
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
