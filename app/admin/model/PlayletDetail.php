<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $playlet_id 短剧
 * @property string $name 名称
 * @property string $content 介绍
 * @property string $price 价格
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletDetail query()
 * @property string $image 封面
 * @property string $video 视频
 * @property int $index 剧集索引
 * @property-read \app\admin\model\Playlet|null $playlet
 * @property int $like_num 点赞人数
 * @property int $play_num 播放次数
 * @mixin \Eloquent
 */
class PlayletDetail extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_playlet_detail';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'playlet_id', 'name', 'content', 'price','video','image'
    ];

    function playlet()
    {
        return $this->belongsTo(Playlet::class, 'playlet_id', 'id');
    }


}
