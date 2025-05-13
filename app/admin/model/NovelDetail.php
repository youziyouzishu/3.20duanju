<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $novel_id 小说
 * @property string $name 章节名称
 * @property string|null $content 内容
 * @property string $price 价格
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NovelDetail query()
 * @property-read \app\admin\model\Novel|null $novel
 * @property-read \app\admin\model\UsersReadLog|null $readLog
 * @property int $index 章节索引
 * @mixin \Eloquent
 */
class NovelDetail extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_novel_detail';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    function novel()
    {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }

    function readLog()
    {
        return $this->hasOne(UsersReadLog::class, 'novel_detail_id', 'id');
    }




}
