<?php
namespace app\admin\model;
use think\Model;
class Goods extends Model
{
    protected $field=true;
    protected static function init()
    {
        Goods::event('before_insert', function ($goods) {
            // 生成商品主图的三张缩略图
            if($_FILES['og_thumb']['tmp_name']){
            $thumbName=$goods->upload('og_thumb');
            $ogThumb=date("Ymd") . DS . $thumbName;
            $bigThumb=date("Ymd") . DS . 'big_'.$thumbName;
            $midThumb=date("Ymd") . DS . 'mid_'.$thumbName;
            $smThumb=date("Ymd") . DS . 'sm_'.$thumbName;
            $image = \think\Image::open(IMG_UPLOADS.$ogThumb);
            $image->thumb(config('big_thumb_width'), config('big_thumb_height'))->save(IMG_UPLOADS.$bigThumb);
            $image->thumb(config('mid_thumb_width'), config('mid_thumb_height'))->save(IMG_UPLOADS.$midThumb);
            $image->thumb(config('sm_thumb_width'), config('sm_thumb_height'))->save(IMG_UPLOADS.$smThumb);
            $goods->og_thumb=$ogThumb;
            $goods->big_thumb=$bigThumb;
            $goods->mid_thumb=$midThumb;
            $goods->sm_thumb=$smThumb;
            }
            //商品编号
            $goods->goods_code=time().rand(100000,999999);
        });

        Goods::event('before_update', function ($goods) {
            $goodsId=$goods->id;
            //新增商品属性  (如果用一下两列的方法，表单提交goods_attr这个数据没值的话，就会出错)
            // $goodsAttr=$goods->goods_attr;
            // $goodsPrice=$goods->goods_price;
            $goodsData=input('post.');
            //处理商品推荐位
            db('rec_item')->where(['value_type'=>1,'value_id'=>$goodsId])->delete();
            if(isset($goodsData['recpos'])){
                foreach ($goodsData['recpos'] as $k => $v) {
                    db('rec_item')->insert(['recpos_id'=>$v,'value_id'=>$goodsId,'value_type'=>1]);
                }
            }
            if(isset($goodsData['goods_attr'])){
                $i=0;
                foreach ($goodsData['goods_attr'] as $k => $v) {
                    if(is_array($v)){
                        if(!empty($v)){
                            foreach ($v as $k1 => $v1) {
                                if(!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$goodsData['attr_price'][$i],'goods_id'=>$goodsId]);
                                $i++;
                            }
                        }
                    }else{
                        //处理唯一属性类型
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //修改商品属性
            if(isset($goodsData['old_goods_attr'])){
                $attrPrice=$goodsData['old_attr_price'];
                $idsArr=array_keys($attrPrice);
                $valuesArr=array_values($attrPrice);
                $i=0;
                foreach ($goodsData['old_goods_attr'] as $k => $v) {
                    if(is_array($v)){
                        if(!empty($v)){
                            foreach ($v as $k1 => $v1) {
                                if(!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->where('id',$idsArr[$i])->update(['attr_value'=>$v1,'attr_price'=>$valuesArr[$i]]);
                                $i++;
                            }
                        }
                    }else{
                        //处理唯一属性类型
                        db('goods_attr')->where('id',$idsArr[$i])->update(['attr_value'=>$v,'attr_price'=>$valuesArr[$i]]);
                        $i++;
                    }
                }
            }

             //商品相册处理
            if($goods->_hasImgs($_FILES['goods_photo']['tmp_name'])){
                $files=request()->file('goods_photo');
                foreach ($files as $file) {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
                    if($info){
                    // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        $photoName=$info->getFilename(); 
                        $ogPhoto=date("Ymd") . DS . $photoName;
                        $bigPhoto=date("Ymd") . DS . 'big_'.$photoName;
                        $midPhoto=date("Ymd") . DS . 'mid_'.$photoName;
                        $smPhoto=date("Ymd") . DS . 'sm_'.$photoName;
                        $image = \think\Image::open(IMG_UPLOADS.$ogPhoto);
                        $image->thumb(config('big_thumb_width'), config('big_thumb_height'))->save(IMG_UPLOADS.$bigPhoto);
                        $image->thumb(config('mid_thumb_width'), config('mid_thumb_height'))->save(IMG_UPLOADS.$midPhoto);
                        $image->thumb(config('sm_thumb_width'), config('sm_thumb_height'))->save(IMG_UPLOADS.$smPhoto);
                        db('goods_photo')->insert(['goods_id'=>$goodsId,'og_photo'=>$ogPhoto,'big_photo'=>$bigPhoto,'mid_photo'=>$midPhoto,'sm_photo'=>$smPhoto]);
                    }else{
                    // 上传失败获取错误信息
                        echo $file->getError();
                    }
                }
            }
            // dump($goods->id); die;
            //处理会员价格
            $mpriceArr=$goods->mp;
            $mp=db('member_price');
            //删除原有会员价格
            $mp->where('goods_id',$goodsId)->delete();
            //批量写入会员价格
            if($mpriceArr){
                foreach ($mpriceArr as $k => $v) {
                    if(trim($v) == ''){
                        continue;
                    }else{
                        $mp->insert(['mlevel_id'=>$k,'mprice'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            // 修改商品前，如果有上传新的缩略图，先处理图片
            if($_FILES['og_thumb']['tmp_name']){
                //如果存在就删除旧的缩略图
                @unlink(IMG_UPLOADS.$goods->og_thumb);
                @unlink(IMG_UPLOADS.$goods->big_thumb);
                @unlink(IMG_UPLOADS.$goods->mid_thumb);
                @unlink(IMG_UPLOADS.$goods->sm_thumb);
                //上传新的缩略图
                $thumbName=$goods->upload('og_thumb');
                $ogThumb=date("Ymd") . DS . $thumbName;
                $bigThumb=date("Ymd") . DS . 'big_'.$thumbName;
                $midThumb=date("Ymd") . DS . 'mid_'.$thumbName;
                $smThumb=date("Ymd") . DS . 'sm_'.$thumbName;
                $image = \think\Image::open(IMG_UPLOADS.$ogThumb);
                $image->thumb(500, 500)->save(IMG_UPLOADS.$bigThumb);
                $image->thumb(200, 200)->save(IMG_UPLOADS.$midThumb);
                $image->thumb(80, 80)->save(IMG_UPLOADS.$smThumb);
                $goods->og_thumb=$ogThumb;
                $goods->big_thumb=$bigThumb;
                $goods->mid_thumb=$midThumb;
                $goods->sm_thumb=$smThumb;
            }
        });

        Goods::afterInsert(function($goods){
            $goodsData=input('post.');
            // dump($goods); die;
            //批量写入会员价格
            $mpriceArr=$goods->mp;
            $goodsId=$goods->id;
            if($mpriceArr){
                foreach ($mpriceArr as $k => $v) {
                    if(trim($v) == ''){
                        continue;
                    }else{
                        db('member_price')->insert(['mlevel_id'=>$k,'mprice'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //处理商品推荐位
            if(isset($goodsData['recpos'])){
                foreach ($goodsData['recpos'] as $k => $v) {
                    db('rec_item')->insert(['recpos_id'=>$v,'value_id'=>$goodsId,'value_type'=>1]);
                }
            }
            //处理商品属性  (如果用一下两列的方法，表单提交goods_attr这个数据没值的话，就会出错)
            // $goodsAttr=$goods->goods_attr;
            // $goodsPrice=$goods->goods_price;
            $i=0;
            if(isset($goodsData['goods_attr'])){
                foreach ($goodsData['goods_attr'] as $k => $v) {
                    if(is_array($v)){
                        if(!empty($v)){
                            foreach ($v as $k1 => $v1) {
                                if(!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$goodsData['attr_price'][$i],'goods_id'=>$goodsId]);
                                $i++;
                            }
                        }
                    }else{
                        //处理唯一属性类型
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //商品相册处理
            if($goods->_hasImgs($_FILES['goods_photo']['tmp_name'])){
                $files=request()->file('goods_photo');
                foreach ($files as $file) {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
                    if($info){
                    // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        $photoName=$info->getFilename(); 
                        $ogPhoto=date("Ymd") . DS . $photoName;
                        $bigPhoto=date("Ymd") . DS . 'big_'.$photoName;
                        $midPhoto=date("Ymd") . DS . 'mid_'.$photoName;
                        $smPhoto=date("Ymd") . DS . 'sm_'.$photoName;
                        $image = \think\Image::open(IMG_UPLOADS.$ogPhoto);
                        $image->thumb(500, 500)->save(IMG_UPLOADS.$bigPhoto);
                        $image->thumb(200, 200)->save(IMG_UPLOADS.$midPhoto);
                        $image->thumb(80, 80)->save(IMG_UPLOADS.$smPhoto);
                        db('goods_photo')->insert(['goods_id'=>$goodsId,'og_photo'=>$ogPhoto,'big_photo'=>$bigPhoto,'mid_photo'=>$midPhoto,'sm_photo'=>$smPhoto]);
                    }else{
                    // 上传失败获取错误信息
                        echo $file->getError();
                    }
                }
            }
        });

        Goods::beforeDelete(function($goods){
            $goodsId=$goods->id;
            //如果该商品设成了推荐，则删除其推荐记录
            db('recItem')->where(['value_id'=>$goodsId,'value_type'=>1])->delete();
            //删除主图及其缩略图
            if($goods->og_thumb){
                $thumb=[];
                $thumb[]=IMG_UPLOADS.$goods->og_thumb;
                $thumb[]=IMG_UPLOADS.$goods->big_thumb;
                $thumb[]=IMG_UPLOADS.$goods->mid_thumb;
                $thumb[]=IMG_UPLOADS.$goods->sm_thumb;
                foreach ($thumb as $k => $v) {
                if(file_exists($v)){
                    @unlink($v);
                }
            }
            }
            //删除关联的会员价格
            db('member_price')->where('goods_id',$goodsId)->delete();
            //删除关联的商品属性
            db('goods_attr')->where('goods_id',$goodsId)->delete();
            //删除关联的商品相册
            $goodsPhotoRes=model('GoodsPhoto')->where('goods_id',$goodsId)->select();
            if(!empty($goodsPhotoRes)){
                foreach ($goodsPhotoRes as $k => $v) {
                       if($v->og_photo){
                        $photo=[];
                        $photo[]=IMG_UPLOADS.$v->og_photo;
                        $photo[]=IMG_UPLOADS.$v->big_photo;
                        $photo[]=IMG_UPLOADS.$v->mid_photo;
                        $photo[]=IMG_UPLOADS.$v->sm_photo;
                        foreach ($photo as $k1 => $v1) {
                            if(file_exists($v1)){
                                @unlink($v1);
                            }
                        }
                    }
                }
            }
            model('GoodsPhoto')->where('goods_id','=',$goodsId)->delete();
        });
    }

    //商品相册是否有图片上传判断
    protected function _hasImgs($tmpArr){
        foreach ($tmpArr as $k => $v) {
            if($v){
                return true;
            }
        }
        return false;
    }

    public function upload($imgName){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file($imgName);
    
    // 移动到框架应用根目录/public/uploads/ 目录下
    if($file){
        $info = $file->move(ROOT_PATH . 'public' . DS . 'static' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            // echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            return $info->getFilename(); 
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}
}
