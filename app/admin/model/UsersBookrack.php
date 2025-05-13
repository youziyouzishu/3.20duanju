<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $novel_id 小说
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBookrack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBookrack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersBookrack query()
 * @property-read \app\admin\model\Novel|null $novel
 * @mixin \Eloquent
 */
class UsersBookrack extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_bookrack';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'novel_id',
        'created_at',
        'updated_at',
    ];

    function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }




}
