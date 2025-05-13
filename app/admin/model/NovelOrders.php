<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $novel_id 小说
 * @property int $novel_detail_id 章节
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelOrders query()
 * @property string $amount 金额
 * @property int $type 购买类型:1=单章,2=整本
 * @property-read \app\admin\model\Novel|null $novel
 * @property-read \app\admin\model\NovelDetail|null $novelDetail
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class NovelOrders extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_novel_orders';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'novel_id',
        'novel_detail_id',
        'amount',
        'type'
    ];

    function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    function novelDetail()
    {
        return $this->belongsTo(NovelDetail::class, 'novel_detail_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



}
