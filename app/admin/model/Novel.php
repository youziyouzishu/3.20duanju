<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 名称
 * @property string $author 作者
 * @property string $image 封面
 * @property int $read_num 阅读量
 * @property int $class_id 分类id
 * @property int $text_num 文字数量
 * @property int $creation_status 状态:0=连载中,1=已完结
 * @property float $score 评分
 * @property string $content 简介
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Novel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Novel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Novel query()
 * @property int $hot 热度
 * @property int $search_num 搜索数量
 * @property int $vip VIP:0=否,1=是
 * @property string|null $end_time 最后更新时间
 * @property string|null $finish_time 完结时间
 * @property int $status 状态:0=下架,1=上架
 * @property-read mixed $creation_status_text
 * @property-read mixed $vip_text
 * @property-read \app\admin\model\Classify|null $class
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\Classify> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\NovelDetail> $detail
 * @property-read \app\admin\model\SystemNotice|null $notice
 * @mixin \Eloquent
 */
class Novel extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_novel';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $appends = ['creation_status_text', 'vip_text'];


    function class()
    {
        return $this->belongsTo(Classify::class, 'class_id', 'id');
    }

    function detail()
    {
        return $this->hasMany(NovelDetail::class, 'novel_id', 'id');
    }

    function tags()
    {
        return $this->belongsToMany(Classify::class, NovelClass::class, 'novel_id', 'class_id')->withTimestamps();
    }

    function getCreationStatusTextAttribute($value)
    {
        $value = $this->creation_status;
        $list = [
            0 => '连载中',
            1 => '已完结',
        ];
        return $list[$value] ?? '';
    }

    function notice()
    {
        return $this->morphOne(SystemNotice::class, 'noticeable');
    }




    function getVipTextAttribute($value)
    {
        $value = $this->vip;
        $list = [
            0 => '否',
            1 => '是',
        ];
        return $list[$value] ?? '';
    }

    function report()
    {
        return $this->morphMany(UsersReport::class, 'reportable','able_type','able_id');
    }


}
