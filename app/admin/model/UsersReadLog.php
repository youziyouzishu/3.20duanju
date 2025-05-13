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
 * @property string $rate 进度
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersReadLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersReadLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UsersReadLog query()
 * @property-read \app\admin\model\Novel|null $novel
 * @property-read \app\admin\model\NovelDetail|null $novelDetail
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UsersReadLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users_read_log';

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
        'rate',
    ];

    function novelDetail()
    {
        return $this->belongsTo(NovelDetail::class, 'novel_detail_id', 'id');
    }

    function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }





}
