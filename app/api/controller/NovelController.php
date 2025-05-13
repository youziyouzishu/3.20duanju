<?php

namespace app\api\controller;

use app\admin\model\Classify;
use app\admin\model\Novel;
use app\admin\model\NovelDetail;
use app\admin\model\NovelOrders;
use app\admin\model\User;
use app\admin\model\UsersBookrack;
use app\admin\model\UsersReadLog;
use app\api\basic\Base;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use support\Request;

class NovelController extends Base
{
    #获取小说列表
    function getNovelList(Request $request)
    {
        $sex = $request->post('sex');#性别:1=男,2=女
        $class_id = $request->post('class_id');#分类id
        $tag_ids = $request->post('tag_ids');#标签ids [1,2,3]
        $type = $request->post('type');#类型:1=推荐榜,2=人气榜,3=新书榜,4=完结榜,5=高分榜,6=热搜榜,7=会员榜
        $keyword = $request->post('keyword');#关键字
        $text_num = $request->post('text_num');#文字数:0=不限,1=10万字以内,2=30万字以内,3=50万字以内,4=30万字以上,5=50万字以上
        $creation_status = $request->post('creation_status');#作品状态:0=不限,1=完结,2=半年内完结,3=连载中,4=3日内更新,5=7日内更新,6=1月内更新
        $sort = $request->post('sort');#排序:1=综合 2=新书 3=高分 4=字数
        $tag_count = !empty($tag_ids) ? count($tag_ids) : 0;
        if ($tag_count >= 4) {
            return $this->fail('标签不能超过3个');
        }
        $rows = Novel::
        where('status', 1)
            ->when(!empty($tag_count), function (Builder $builder) use ($tag_ids) {
                $builder->whereHas('tags', function ($query) use ($tag_ids) {
                    $query->whereIn('wa_classify.id', $tag_ids);
                });
            })
            ->with(['tags'])
            ->when(!empty($creation_status), function (Builder $builder) use ($creation_status) {
                if ($creation_status == 1) {
                    $builder->where('creation_status', 1);
                }
                if ($creation_status == 2) {
                    $builder->where('finish_time', '>', Carbon::now()->subMonths(6));
                }
                if ($creation_status == 3) {
                    $builder->where('creation_status', 0);
                }
                if ($creation_status == 4) {
                    $builder->where('end_time', '>', Carbon::now()->subDays(3));
                }
                if ($creation_status == 5) {
                    $builder->where('end_time', '>', Carbon::now()->subDays(7));
                }
                if ($creation_status == 6) {
                    $builder->where('end_time', '>', Carbon::now()->subMonths(1));
                }
            })
            ->when(!empty($text_num), function (Builder $builder) use ($text_num) {
                if ($text_num == 1) {
                    $builder->where('text_num', '<=', 100000);
                }
                if ($text_num == 2) {
                    $builder->where('text_num', '<=', 300000);
                }
                if ($text_num == 3) {
                    $builder->where('text_num', '<=', 500000);
                }
                if ($text_num == 4) {
                    $builder->where('text_num', '>=', 300000);
                }
                if ($text_num == 5) {
                    $builder->where('text_num', '>=', 500000);
                }
            })
            ->when(!empty($keyword), function (Builder $query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')->orWhere('name', 'like', '%' . $keyword . '%');
            })
            ->when(!empty($class_id), function (Builder $query) use ($class_id) {
                $query->where('class_id', $class_id);
            })
            ->when(!empty($sex), function (Builder $query) use ($sex) {
                $class_ids = Classify::where('type', $sex)->where('pid', 0)->pluck('id')->toArray();
                $query->whereIn('class_id', $class_ids);
            })
            ->when(!empty($type), function (Builder $query) use ($type) {
                if ($type == 1) {
                    $query->orderByDesc('read_num');
                }
                if ($type == 2) {
                    $query->orderByDesc('hot');
                }
                if ($type == 3) {
                    $query->where('created_at', '>', Carbon::now()->subDays(7))->orderByDesc('hot');
                }
                if ($type == 4) {
                    $query->where('creation_status', 1)->orderByDesc('read_num');
                }
                if ($type == 5) {
                    $query->orderByDesc('score');
                }
                if ($type == 6) {
                    $query->orderByDesc('search_num');
                }
                if ($type == 7) {
                    $query->where('vip', 1)->orderByDesc('read_num');
                }
            })
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    #获取小说详情
    function getNovelDetail(Request $request)
    {
        $novel_id = $request->post('novel_id');
        $novel = Novel::find($novel_id);
        $novel->setAttribute('bookrack_status', UsersBookrack::where('user_id', $request->user_id)->where('novel_id', $novel_id)->exists());
        $readlog = UsersReadLog::where('user_id', $request->user_id)->where('novel_id', $novel_id)->first();
        if ($readlog) {
            $novel->setAttribute('chapter_index', $readlog->novelDetail->index);
            $novel->setAttribute('chapter_name', $readlog->novelDetail->name);
        } else {
            $novel->setAttribute('chapter_index', '');
            $novel->setAttribute('chapter_name', '');
        }
        return $this->success('成功', $novel);
    }

    #章节列表
    function getChapterList(Request $request)
    {
        $order = $request->post('order', 'asc');
        $novel_id = $request->post('novel_id');
        $rows = NovelDetail::with(['readLog' => function ($builder) use ($request) {
            $builder->where('user_id', $request->user_id);
        }])->where('novel_id', $novel_id)->orderBy('index', $order)->paginate()->items();
        $lock = NovelOrders::where('user_id', $request->user_id)->where('novel_id', $novel_id)->where('type', 2)->exists();#整本
        foreach ($rows as $row) {
            $chapter = NovelOrders::where('user_id', $request->user_id)->where('novel_detail_id', $row->id)->where('type', 1)->exists();
            if ($lock || $chapter || $row->price == 0) {
                $row->setAttribute('lock', false);
            } else {
                $row->setAttribute('lock', true);
            }
        }
        return $this->success('成功', $rows);
    }

    #章节详情
    function getChapterDetail(Request $request)
    {
        $detail_id = $request->post('detail_id');
        if (!$detail_id) {
            return $this->fail('参数不能为空');
        }
        #获取小说详情
        $row = NovelDetail::with(['readLog' => function ($builder) use ($request) {
            $builder->where('user_id', $request->user_id);
        }])->find($detail_id);
        $lock = NovelOrders::where('user_id', $request->user_id)->where('novel_id', $row->novel_id)->where('type', 2)->exists();#整本
        $chapter = NovelOrders::where('user_id', $request->user_id)->where('novel_detail_id', $row->id)->where('type', 1)->exists();
        if ($lock || $chapter || $row->price == 0) {
            $row->setAttribute('lock', false);
        } else {
            $row->setAttribute('lock', true);
        }
        $buy_chapter = NovelOrders::where('user_id', $request->user_id)->where('novel_id', $row->novel_id)->where('type', 1)->pluck('novel_detail_id')->toArray();
        #后面的5章
        $next = NovelDetail::where('novel_id', $row->novel_id)->where(function (Builder $query)use($lock,$buy_chapter){
            if (!$lock){
                $query->orWhere('price',0)->orWhereIn('id',$buy_chapter);
            }
        })->where('index', '>', $row->index)->take(5)->get();
        return $this->success('成功', ['current'=>$row,'next'=>$next]);
    }


    #详情-推荐
    function getRecommendOfNovel(Request $request)
    {
        $novel_id = $request->post('novel_id');
        $row = Novel::find($novel_id);
        $rows = Novel::where('class_id', $row->class_id)->where('id', '<>', $novel_id)->inRandomOrder()->take(4)->get();
        return $this->success('成功', $rows);
    }

    #上下书架
    function upBookrack(Request $request)
    {
        $novel_id = $request->post('novel_id');
        $row = UsersBookrack::where('user_id', $request->user_id)->where('novel_id', $novel_id)->first();
        if ($row) {
            $row->delete();
            $result = false;
        } else {
            UsersBookrack::create(['user_id' => $request->user_id, 'novel_id' => $novel_id]);
            $result = true;
        }
        return $this->success('成功', $result);
    }

    #获取书架列表
    function getBookrackList(Request $request)
    {
        $rows = UsersBookrack::with(['novel'])->where('user_id', $request->user_id)->orderByDesc('id')->paginate()->items();
        foreach ($rows as $row) {
            $row->setAttribute('bookrack_status', UsersBookrack::where('user_id', $request->user_id)->where('novel_id', $row->novel_id)->exists());
        }
        return $this->success('成功', $rows);
    }

    #删除书架
    function deleteBookrack(Request $request)
    {
        $bookrack_id = $request->post('bookrack_id');
        UsersBookrack::find($bookrack_id)->delete();
        return $this->success('成功');
    }

    #同步小说阅读进度
    function updateReadLog(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $rate = $request->post('rate');
        $detail = NovelDetail::find($detail_id);
        UsersReadLog::updateOrCreate(['user_id' => $request->user_id, 'novel_id' => $detail->novel_id], ['novel_detail_id' => $detail_id, 'novel_id' => $detail->novel_id, 'user_id' => $request->user_id, 'rate' => $rate]);
        return $this->success('成功');
    }

    #查询购买整本价格
    function getPrice(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $chapter = NovelDetail::find($detail_id);
        $user = User::find($request->user_id);
        if (!$user) {
            return $this->fail('用户不存在');
        }
        //查询出剩余未购买的章节
        $totalPrice = NovelDetail::where('novel_id', $chapter->novel_id)->whereNotIn('id', function ($query) use ($request, $chapter) {
            $query->select('novel_detail_id')->from('wa_novel_orders')->where('novel_id', $chapter->novel_id)->where('user_id', $request->user_id)->where('type', 1);
        })->sum('price');
        return $this->success('成功', ['total_price' => $totalPrice, 'balance' => $user->money]);
    }

    // 购买章节
    public function purchaseChapter(Request $request)
    {
        $detail_id = $request->post('detail_id');
        $user = User::find($request->user_id);
        $chapter = NovelDetail::find($detail_id);

        if (!$user || !$chapter) {
            return $this->fail('用户或章节不存在');
        }

        $lock = NovelOrders::where('user_id', $request->user_id)->where('novel_id', $chapter->novel_id)->where('type', 2)->exists();#整本
        if ($lock) {
            return $this->fail('整本已购买');
        }

        $already = NovelOrders::where('user_id', $request->user_id)->where('novel_detail_id', $detail_id)->where('type', 1)->exists();
        if ($already) {
            return $this->fail('已购买过此章节');
        }

        if ($user->money < $chapter->price) {
            return $this->fail('金币不足');
        }

        User::score(-$chapter->price, $request->user_id, '购买章节', 'money');

        // 记录购买记录
        NovelOrders::create([
            'user_id' => $request->user_id,
            'novel_id' => $chapter->novel_id,
            'novel_detail_id' => $detail_id,
            'amount' => $chapter->price,
            'type' => 1
        ]);
        return $this->success('购买成功');
    }

    // 购买整本小说
    public function purchaseNovel(Request $request)
    {
        $detail_id = $request->post('detail_id');

        $user = User::find($request->user_id);
        $chapter = NovelDetail::find($detail_id);

        if (!$user || !$chapter) {
            return $this->fail('用户或章节不存在');
        }

        $already = NovelOrders::where('user_id', $request->user_id)->where('novel_id', $chapter->novel_id)->where('type', 2)->exists();
        if ($already) {
            return $this->fail('已购买过整本小说');
        }
        //查询出剩余未购买的章节
        $totalPrice = NovelDetail::where('novel_id', $chapter->novel_id)->whereNotIn('id', function ($query) use ($request, $chapter) {
            $query->select('novel_detail_id')->from('wa_novel_orders')->where('user_id', $request->user_id)->where('novel_id', $chapter->novel_id)->where('type', 1);
        })->sum('price');


        if ($user->money < $totalPrice) {
            return $this->fail('金币不足');
        }

        // 扣除用户金币
        User::score(-$totalPrice, $request->user_id, '购买小说', 'money');

        // 记录购买记录
        NovelOrders::create([
            'user_id' => $request->user_id,
            'novel_id' => $chapter->novel_id,
            'amount' => $totalPrice,
            'type' => 2
        ]);
        return $this->success('购买成功');
    }

    #小说下载列表
    function getDownloadList(Request $request)
    {
        $novel_id = $request->post('novel_id');
        $novel = Novel::find($novel_id);
        if (!$novel) {
            return $this->fail('小说不存在');
        }
        // 查询小说的所有章节
        $chapters = NovelDetail::where('novel_id', $novel_id)->orderBy('index')->get();
        $lock = NovelOrders::where('user_id', $request->user_id)
            ->where('novel_id', $novel_id)
            ->where('type', 2)
            ->exists();#整本
        // 批量查询用户是否购买了各个章节
        $purchasedChapters = NovelOrders::where('user_id', $request->user_id)
            ->where('novel_id', $novel_id)
            ->where('type', 1)
            ->pluck('novel_detail_id')
            ->toArray();

        // 将每30章节算一个集合
        $rows = $chapters->chunk(30)->map(function ($chunk) use ($purchasedChapters, $lock) {
            $chunk->each(function ($chapter) use (&$purchasedChapters, &$lock) {
                $chapter->setAttribute('lock', !($lock || in_array($chapter->id, $purchasedChapters) || $chapter->price == 0));
            });

            $startIndex = $chunk->first()->index;
            $endIndex = $chunk->last()->index;
            return [
                'title' => "第{$startIndex}章 - 第{$endIndex}章",
                'children' => $chunk->toArray()
            ];
        });
        return $this->success('成功', $rows);
    }
}
