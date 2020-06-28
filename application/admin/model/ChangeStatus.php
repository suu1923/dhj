<?php
/**
 * Created by PhpStorm.
 * User: Suu
 * Date: 2020/6/18
 * Time: 16:45
 */

namespace app\admin\model;


use think\Model;

class ChangeStatus extends Model
{
    // 表名
    protected $name = 'change_state';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';

    protected $updateTime = false;

}
