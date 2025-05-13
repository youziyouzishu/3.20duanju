<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property string $title 标题
 * @property string $description 描述
 * @property string $content 详情
 * @property string $image 图片
 * @property integer $noticeable_id 关联ID
 * @property string $noticeable_type 关联模型
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $noticeable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemNotice query()
 * @mixin \Eloquent
 */
class SystemNotice extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_system_notice';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'description',
        'content',
        'image',
        'noticeable_id',
        'noticeable_type',
    ];

    public function noticeable()
    {
        return $this->morphTo(__FUNCTION__, 'noticeable_type', 'noticeable_id');
    }



}
