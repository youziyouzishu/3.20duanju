<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;



/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $withdraw_amount 提现金额
 * @property string $chance_amount 手续费
 * @property string $into_amount 到账金额
 * @property string $ali_name 支付宝名称
 * @property string $ali_account 支付宝账号
 * @property string $chance_rate 手续费比例
 * @property int $status 状态:0=待审核,1=已打款,2=驳回
 * @property string $reason 驳回原因
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersWithdraw query()
 * @property-read mixed $status_text
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UsersWithdraw extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_withdraw';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $appends = [
        'status_text',
    ];

    protected $fillable = [
        'user_id',
        'withdraw_amount',
        'chance_amount',
        'into_amount',
        'ali_name',
        'ali_account',
        'chance_rate',
        'reason',
    ];

    function getStatusTextAttribute($value)
    {
        $value = $this->status;
        $list = [
            0 => '待审核',
            1 => '已到账',
            2 => '驳回',
        ];
        return $list[$value] ?? '';
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
