<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $novel_id 小说
 * @property integer $class_id 分类
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelClass query()
 * @mixin \Eloquent
 */
class NovelClass extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_novel_class';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
