<?php

namespace app\api\controller;

use app\admin\model\Playlet;
use app\admin\model\PlayletDetail;
use app\admin\model\PlayletOrders;
use app\admin\model\User;
use app\admin\model\UsersPlayletFollow;
use app\admin\model\UsersPlayletLike;
use app\admin\model\UsersPlayletLog;
use app\api\basic\Base;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use support\Request;

class PlayletController extends Base
{


    #获取列表
    function getPlayletList(Request $request)
    {

        $type = $request->post('type');#类型:0=全部 1=本周热门 2=本周排行 3=最近热播
        $tag_ids = $request->post('tag_ids');#标签ids [1,2,3]
        $serie_num = $request->post('serie_num');#集数 0=不限 1=1-30集 ，2=30-60 ，3=60-90，4=90-120，5=120以上
        $keyword = $request->post('keyword');
        $tag_count = !empty($tag_ids) ? count($tag_ids) : 0;
        if ($tag_count >= 4) {
            return $this->fail('标签不能超过3个');
        }
        $rows = Playlet::where('status', 1)
            ->withCount('detail')
            ->when(!empty($keyword), function (Builder $builder) use ($keyword) {
                $builder->where('name', 'like', '%' . $keyword . '%');
            })
            ->when(!empty($serie_num),function (Builder $query)use($serie_num){
                if ($serie_num == 1){
                    $query->has('detail','>=',1)->has('detail', '<=', 30);
                }
                if ($serie_num == 2){
                    $query->has('detail','>=',30)->has('detail', '<=', 60);
                }
                if ($serie_num == 3){
                    $query->has('detail','>=',60)->has('detail', '<=', 90);
                }
                if ($serie_num == 4){
                    $query->has('detail','>=',90)->has('detail', '<=', 120);
                }
                if ($serie_num == 5){
                    $query->has('detail','>=',120);
                }
            })
            ->when(!empty($type), function (Builder $builder) use ($type) {
                if ($type == 1) {
                    // 获取本周的开始和结束日期
                    $startOfWeek = Carbon::now()->startOfWeek();
                    $endOfWeek = Carbon::now()->endOfWeek();
                    $builder->whereBetween('created_at', [$startOfWeek, $endOfWeek])->orderByDesc('like_num');
                }
                if ($type == 2) {
                    // 获取本周的开始和结束日期
                    $startOfWeek = Carbon::now()->startOfWeek();
                    $endOfWeek = Carbon::now()->endOfWeek();
                    $builder->whereBetween('created_at', [$startOfWeek, $endOfWeek])->orderByDesc('hot');
                }
                if ($type == 3) {
                    $builder->orderByDesc('id');
                }
            })
            ->when(!empty($tag_count), function (Builder $builder) use ($tag_ids) {
                $builder->whereHas('tags', function ($query) use ($tag_ids) {
                    $query->whereIn('wa_classify.id', $tag_ids);
                });
            })
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }


    #获取详情
    function getPlayletDetail(Request $request)
    {
        $playlet_id = $request->post('playlet_id');
        $row = Playlet::with(['tags'])->withCount(['detail'])->find($playlet_id);
        $follow_status = UsersPlayletFollow::where('user_id', $request->user_id)->where('playlet_id', $playlet_id)->exists();
        $log = UsersPlayletLog::where('user_id', $request->user_id)->where('playlet_id', $playlet_id)->first();
        $lock = PlayletOrders::where('user_id', $request->user_id)->where('playlet_id', $playlet_id)->where('type', 2)->exists();#整本
        if ($log) {
            $juji = PlayletOrders::where('user_id', $request->user_id)->where('playlet_detail_id', $log->playletDetail->id)->where('type', 1)->exists();
            $row->setAttribute('video', $log->playletDetail);
            if ($lock || $juji || $log->playletDetail->price == 0) {
                $row->setAttribute('lock', false);
            } else {
                $row->setAttribute('lock', true);
            }
        } else {
            $detail = $row->detail()->orderBy('index')->first();
            $juji = PlayletOrders::where('user_id', $request->user_id)->where('playlet_detail_id', $detail->id)->where('type', 1)->exists();
            $row->setAttribute('video', $detail);
            if ($lock || $juji || $detail->price == 0) {
                $row->setAttribute('lock', false);
            } else {
                $row->setAttribute('lock', true);
            }
        }
        $row->setAttribute('follow_status', $follow_status);
        return $this->success('成功', $row);
    }

    function getSerieList(Request $request)
    {
        $playlet_id = $request->post('playlet_id');
        $row = Playlet::find($playlet_id);
        $rows = $row->detail()->orderBy('index')->get();
        $follow_status = UsersPlayletFollow::where('user_id', $request->user_id)->where('playlet_id', $playlet_id)->exists();
        $lock = PlayletOrders::where('user_id', $request->user_id)->where('playlet_id', $playlet_id)->where('type', 2)->exists();#整本
        foreach ($rows as $detail) {
            $juji = PlayletOrders::where('user_id', $request->user_id)->where('playlet_detail_id', $detail->id)->where('type', 1)->exists();
            if ($lock || $juji || $detail->price == 0) {
                $detail->setAttribute('lock', false);
            } else {
                $detail->setAttribute('lock', true);
            }
            $detail->setAttribute('follow_status', $follow_status);
            $detail->setAttribute('like_status', UsersPlayletLike::where('user_id', $request->user_id)->where('playlet_detail_id', $detail->id)->exists());
        }
        return $this->success('成功', $rows);
    }

    #详情-推荐
    function getRecommendOfPlaylet(Request $request)
    {
        $playlet_id = $request->post('playlet_id');
        $row = Playlet::find($playlet_id);
        $rows = Playlet::where('class_id', $row->class_id)->where('id', '<>', $playlet_id)->inRandomOrder()->take(4)->get();
        return $this->success('成功', $rows);
    }

    function getSerieDetail(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $row = PlayletDetail::find($detail_id);
        $row->increment('play_num');
        $row->playlet()->increment('play_num');
        $lock = PlayletOrders::where('user_id', $request->user_id)->where('playlet_id', $row->playlet_id)->where('type', 2)->exists();#整本
        $juji = PlayletOrders::where('user_id', $request->user_id)->where('playlet_detail_id', $row->id)->where('type', 1)->exists();
        if ($lock || $juji || $row->price == 0) {
            $row->setAttribute('lock', false);
        } else {
            $row->setAttribute('lock', true);
        }
        return $this->success('成功', $row);
    }

    #同步短剧观看进度
    function updatePlayletLog(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $rate = $request->post('rate');
        $detail = PlayletDetail::find($detail_id);
        UsersPlayletLog::updateOrCreate(['user_id' => $request->user_id, 'playlet_id' => $detail->playlet_id], ['playlet_detail_id' => $detail_id, 'playlet_id' => $detail->playlet_id, 'user_id' => $request->user_id]);
        return $this->success('成功');
    }

    #点赞
    function like(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $detail = PlayletDetail::find($detail_id);
        $row = UsersPlayletLike::where('user_id', $request->user_id)->where('playlet_detail_id', $detail->id)->first();
        if ($row) {
            $row->playlet()->decrement('like_num');
            $row->playletDetail()->increment('like_num');
            $row->delete();
            $result = false;
        } else {

            $row = UsersPlayletLike::create(['user_id' => $request->user_id, 'playlet_detail_id' => $detail->id, 'playlet_id' => $detail->playlet_id]);
            $row->playlet()->increment('like_num');
            $row->playletDetail()->increment('like_num');
            $result = true;
        }
        return $this->success('成功', $result);
    }

    #追剧
    function follow(Request $request)
    {
        $playlet_id = $request->post('playlet_id');
        $row = UsersPlayletFollow::where('user_id', $request->user_id)->where('playlet_id', $playlet_id)->first();
        if ($row) {
            $row->delete();
            $result = false;
        } else {
            UsersPlayletFollow::create(['user_id' => $request->user_id, 'playlet_id' => $playlet_id]);
            $result = true;
        }
        return $this->success('成功', $result);
    }


    #查询购买整本价格
    function getPrice(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $juji = PlayletDetail::find($detail_id);
        $user = User::find($request->user_id);
        //查询出剩余未购买的章节
        $totalPrice = PlayletDetail::where('playlet_id', $juji->playlet_id)->whereNotIn('id', function ($query) use ($request, $juji) {
            $query->select('playlet_detail_id')->from('wa_playlet_orders')->where('playlet_id', $juji->playlet_id)->where('user_id', $request->user_id)->where('type', 1);
        })->sum('price');
        return $this->success('成功', ['total_price' => $totalPrice, 'balance' => $user->money]);
    }

    // 购买章节
    public function purchaseSerie(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $user = User::find($request->user_id);
        $juji = PlayletDetail::find($detail_id);

        if (!$user || !$juji) {
            return $this->fail('用户或剧集不存在');
        }


        $already = PlayletOrders::where('user_id', $request->user_id)->where('playlet_detail_id', $detail_id)->where('type', 1)->exists();
        if ($already) {
            return $this->fail('已购买过此剧集');
        }

        if ($user->money < $juji->price) {
            return $this->fail('金币不足');
        }

        User::score(-$juji->price, $request->user_id, '购买剧集', 'money');

        // 记录购买记录
        PlayletOrders::create([
            'user_id' => $request->user_id,
            'playlet_id' => $juji->playlet_id,
            'playlet_detail_id' => $detail_id,
            'amount' => $juji->price,
            'type' => 1
        ]);
        return $this->success('购买成功');
    }

    // 购买整本小说
    public function purchasePlaylet(Request $request)
    {
        $detail_id = $request->post('detail_id');

        $user = User::find($request->user_id);
        $juji = PlayletDetail::find($detail_id);

        if (!$user || !$juji) {
            return $this->fail('用户或剧集不存在');
        }

        $already = PlayletOrders::where('user_id', $request->user_id)->where('playlet_id', $juji->playlet_id)->where('type', 2)->exists();
        if ($already) {
            return $this->fail('已购买过整个短剧');
        }

        $totalPrice = PlayletDetail::where('playlet_id', $juji->playlet_id)->whereNotIn('id', function ($query) use ($request, $juji) {
            $query->select('playlet_detail_id')->from('wa_playlet_orders')->where('user_id', $request->user_id)->where('playlet_id', $juji->playlet_id)->where('type', 1);
        })->sum('price');


        if ($user->money < $totalPrice) {
            return $this->fail('金币不足');
        }

        // 扣除用户金币
        User::score(-$totalPrice, $request->user_id, '购买小说', 'money');

        // 记录购买记录
        PlayletOrders::create([
            'user_id' => $request->user_id,
            'playlet_id' => $juji->playlet_id,
            'amount' => $totalPrice,
            'type' => 2
        ]);
        return $this->success('购买成功');
    }


}
