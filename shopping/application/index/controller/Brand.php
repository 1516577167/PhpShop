<?php
namespace app\index\controller;
use think\Controller;
class Brand extends Controller
{
    public function lst()
    {
        $data=[];
        $brandRes=db('brand')->order('id DESC')->paginate(17);
        $data['totalPage']=$brandRes->lastPage();
        $brands=$brandRes->items();
        $html='';
        $html.='<div class="brand-list" id="recommend_brands"><ul>';
        foreach ($brands as $k => $v) {
            $html.='<li>
                        <div class="brand-img"><a href="'.$v['brand_url'].'" target="_blank"><img src="'.config('view_replace_str.__uploads__').'/'.$v['brand_img'].'"></a>
                        </div>  
                    </li>';
        }
        $html.='</ul><a href="javascript:void(0);" ectype="changeBrand" class="refresh-btn"><i class="iconfont icon-rotate-alt"></i><span>换一批</span></a>';
        $data['brands']=$html;
        return json($data);
    }

}
