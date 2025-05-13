<?php

namespace app\admin\model;


use Illuminate\Database\Eloquent\Relations\Pivot;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $able_id 关联ID
 * @property string $able_type 关联模型
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersReport query()
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reportable
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UsersReport extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_report';
    protected $connection = 'plugin.admin.mysql';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',
        'able_id',
        'able_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function reportable()
    {
        return $this->morphTo(__FUNCTION__,'able_type','able_id');
    }





}
