<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $playlet_id 短剧
 * @property int $playlet_detail_id 剧集
 * @property string $rate 进度
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersPlayletLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersPlayletLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersPlayletLog query()
 * @property-read \app\admin\model\Playlet|null $playlet
 * @property-read \app\admin\model\PlayletDetail|null $playletDetail
 * @mixin \Eloquent
 */
class UsersPlayletLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_playlet_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'playlet_id',
        'playlet_detail_id',
        'rate',
    ];

    function playlet()
    {
        return $this->belongsTo(Playlet::class, 'playlet_id', 'id');
    }

    function playletDetail()
    {
        return $this->belongsTo(PlayletDetail::class, 'playlet_detail_id', 'id');
    }






}
