<?php

namespace app\admin\model;


use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $class_id 分类
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersClass query()
 * @mixin \Eloquent
 */
class UsersClass extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_class';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';



    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s'); // 自定义日期格式
    }


    protected $fillable = [
        'user_id',
        'class_id',
    ];




}
