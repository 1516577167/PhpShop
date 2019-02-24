<?php
namespace app\admin\controller;
use think\Controller;
class Conf extends Controller
{
    public function conflist(){
        $conf=db('conf');
        if(request()->isPost()){
            $data=input('post.');
            //复选框空选问题     如果提交界面的复选框没有选中一个按钮，那么复选框的那个值不会被传递过来，就是更改操作时复选框那个字段只会保存修改之前的值
            $checkFields2D=$conf->field('ename')->where('form_type','checkbox')->select();
            //拼装成一维数组
            $checkFields=[];
            if($checkFields2D){
                foreach ($checkFields2D as $k => $v) {
                    $checkFields[]=$v['ename'];
                }
            }
            //所有发送的字段组成的数据
            $allFields=[];
            // 处理文字数据
            foreach ($data as $k => $v) {
                $allFields[]=$k;
                if(is_array($v)){
                    $value=implode(',',$v);
                    $conf->where(['ename'=>$k])->update(['value'=>$value]);
                }else{
                    $conf->where(['ename'=>$k])->update(['value'=>$v]);
                }
            }
            //如果复选框未选中任何一个选项，则设置空
            foreach ($checkFields as $k => $v) {
                if(!in_array($v,$allFields)){
                    $conf->where('ename',$v)->update(['value'=>'']);
                }
            }
            //处理图片数据
            if($_FILES){
                foreach ($_FILES as $k => $v) {
                    if($v['tmp_name']){
                        $imgs=$conf->field('value')->where('ename',$k)->find();
                        if($imgs['value']){
                            $oldImg=IMG_UPLOADS.$imgs['value'];
                            if(file_exists($oldImg)){
                                @unlink($oldImg);
                            }
                        }
                        $imgSrc=$this->upload($k);
                        $conf->where('ename',$k)->update(['value'=>$imgSrc]);
                    }
                }
            }
            $this->success('配置成功');
        }
        $ShopConfRes=$conf->where('conf_type',1)->order('sort DESC')->select();
        $GoodsConfRes=$conf->where('conf_type',2)->order('sort DESC')->select();
        $this->assign([
            'ShopConfRes'=>$ShopConfRes,
            'GoodsConfRes'=>$GoodsConfRes,
        ]);
        return view();
    }

    public function lst()
    {
        $conf=db('conf');
        if(request()->isPost()){
            $data=input('post.');   
            foreach ($data['sort'] as $k => $v) {
                $conf->where('id',$k)->update(['sort'=>$v]);
            }
            $this->success('排序成功');
        }
        $confRes=$conf->order('sort DESC')->paginate(6);
        $this->assign([
            'confRes'=>$confRes,
        ]);
        return view('list');
    }

    public function add()
    {
        if(request()->isPost()){
            $data=input('post.');
            //如果是多选，替换中文“，”
            if($data['form_type']=='radio'||$data['form_type']=='select'||$data['form_type']=='checkbox'||$data['form_type']=='textarea'){
                $data['values']=str_replace('，', ',', $data['values']);
                $data['value']=str_replace('，', ',', $data['value']);
            }
            // 验证
            $validate = validate('conf');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $add=db('conf')->insert($data);
            if($add){
             $this->success('添加配置成功！','lst'); 
         }else{
            $this->error('添加配置失败！');
        }
        return;
    }
    return view();
}

public function edit()
{
    if(request()->isPost()){
        $data=input('post.');
        if($data['form_type']=='radio'||$data['form_type']=='select'||$data['form_type']=='checkbox'||$data['form_type']=='textarea'){
                $data['values']=str_replace('，', ',', $data['values']);
                $data['value']=str_replace('，', ',', $data['value']);
            }
            // 验证
        $validate = validate('conf');
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $save=db('conf')->update($data);
        if($save !== false){
         $this->success('修改配置成功！','lst'); 
     }else{
        $this->error('修改配置失败！');
    }
    return;
}
$id=input('id');
$confs=db('conf')->find($id);
$this->assign([
    'confs'=>$confs,
]);
return view();
}

public function del($id)
{
    $del=db('conf')->delete($id);
    if($del){
     $this->success('删除配置成功！','lst'); 
 }else{
    $this->error('删除配置失败！');
}
}

   //上传图片的操作类
public function upload($imgName){
            // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file($imgName);
        
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
