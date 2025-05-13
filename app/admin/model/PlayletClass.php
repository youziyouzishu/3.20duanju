<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $playlet_id 短剧
 * @property int $class_id 分类
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlayletClass query()
 * @mixin \Eloquent
 */
class PlayletClass extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_playlet_class';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'playlet_id', 'class_id',
    ];



}
