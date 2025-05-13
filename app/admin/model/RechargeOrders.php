<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $pay_type 支付方式:1=微信,2=支付宝
 * @property string $ordersn 订单编号
 * @property string $pay_amount 支付金额
 * @property int $status 状态:0=未支付,1=已支付
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RechargeOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RechargeOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RechargeOrders query()
 * @property-read \app\admin\model\User|null $user
 * @property string|null $pay_time 支付时间
 * @property-read mixed $pay_type_text
 * @property-read mixed $status_text
 * @mixin \Eloquent
 */
class RechargeOrders extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_recharge_orders';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'pay_type',
        'ordersn',
        'pay_amount',
    ];

    protected $appends = [
        'pay_type_text','status_text'
    ];

    function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    function getPayTypeTextAttribute($value)
    {
        $value = $value ?: ($this->pay_type ?? '');
        $list = [
            1 => '微信',
            2 => '支付宝',
        ];
        return $list[$value] ?? '';
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ?: ($this->status ?? '');
        $list = [
            0 => '未支付',
            1 => '已支付',
        ];
        return $list[$value] ?? '';
    }


}
