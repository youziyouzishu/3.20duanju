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
 * @property string $amount 金额
 * @property int $type 购买类型:1=单集,2=整集
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletOrders query()
 * @property-read \app\admin\model\Playlet|null $playlet
 * @property-read \app\admin\model\PlayletDetail|null $playletDetail
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class PlayletOrders extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_playlet_orders';

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
        'amount',
        'type',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function playlet()
    {
        return $this->belongsTo(Playlet::class, 'playlet_id', 'id');
    }

    function playletDetail()
    {
        return $this->belongsTo(PlayletDetail::class, 'playlet_detail_id', 'id');
    }




}
