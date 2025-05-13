<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $class_name 分类名称
 * @property string $content 内容
 * @property string $images 图片
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Advice query()
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class Advice extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_advice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected  $fillable = [
        'user_id',
        'class_name',
        'content',
        'images',
    ];

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
