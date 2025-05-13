<?php

namespace app\admin\model;


use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 月会员
 * @property string $original_price 原价
 * @property string $price 现价
 * @property string $mark 备注
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vip query()
 * @mixin \Eloquent
 */
class Vip extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_vip';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';



}
