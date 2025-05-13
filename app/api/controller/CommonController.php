<?php

namespace app\api\controller;

use app\admin\model\Banner;
use app\admin\model\Classify;
use app\admin\model\Help;
use app\admin\model\SystemNotice;
use app\admin\model\Vip;
use app\api\basic\Base;
use Illuminate\Database\Eloquent\Builder;
use plugin\admin\app\model\Dict;
use plugin\admin\app\model\Option;
use support\Request;
use support\Response;

class CommonController extends Base
{
    protected $noNeedLogin = ['*'];

    #获取轮播图
    function getBannerList(Request $request)
    {
        $type = $request->post('type');#板块:1=推荐,2=短剧,3=男生,4=女生
        $rows = Banner::where(['type'=>$type])->orderByDesc('weigh')->get();
        return $this->success('成功',$rows);
    }

    #获取vip列表
    function getVipList(Request $request)
    {
        $rows = Vip::all();
        return $this->success('成功',$rows);
    }


    #获取分类
    function getClass(Request $request)
    {
        $type = $request->post('type');#类型:1=男生,2=女生,3=短剧分类,4=推荐分类,5=男生子类,6=女生子类,7=短剧子类
        $rows = Classify::when(!empty($type),function (Builder $builder)use($type){
            if ($type == 1 || $type == 2 || $type == 3){
                $builder->with(['children'])->where('pid',0)->where(['type'=>$type]);
            }
            if ($type == 4){
                $builder->where('pid','<>',0)->whereIn('type',[1,2]);
            }
            if ($type == 5){
                $builder->where('pid','<>',0)->where('type',1);
            }
            if ($type == 6){
                $builder->where('pid','<>',0)->where('type',2);
            }
            if ($type == 7){
                $builder->where('pid','<>',0)->where('type',3);
            }

        })->orderByDesc('weigh')->get();
        return $this->success('成功',$rows);
    }

    #获取配置
    function getConfig()
    {
        $name = 'admin_config';
        $config = Option::where('name', $name)->value('value');
        $config = json_decode($config);
        return $this->success('成功', $config);
    }

    #获取帮助列表
    function getHelpList()
    {
        $rows = Help::all();
        return $this->success('成功', $rows);
    }

    #获取字典
    public function getDict(Request $request): Response
    {
        $name = $request->post('name');
        return $this->json(0, 'ok', (array)Dict::get($name));
    }


    function getSystemNotice(Request $request)
    {
        $rows = SystemNotice::orderByDesc('id')->paginate()->items();
        return $this->success('成功', $rows);
    }

}
