<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 名称
 * @property int $weigh 权重
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classify newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classify newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Classify query()
 * @property int $type 类型:1=男生,2=女生,3=短剧分类
 * @property int $pid 父级
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Classify> $children
 * @property-read Classify|null $parent
 * @mixin \Eloquent
 */
class Classify extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_classify';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    function children()
    {
        return $this->hasMany(Classify::class, 'pid', 'id');
    }

    function parent()
    {
        return $this->belongsTo(Classify::class, 'pid', 'id');
    }






}
