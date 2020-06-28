<?php
/**
 * Created by PhpStorm.
 * User: Suu
 * Date: 2020/5/8
 * Time: 14:03
 */

namespace app\admin\model;


use think\Model;

class Msg extends Model
{
    // 表名
    protected $name = 'msg';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;


    protected function getContentAttr($value){
        return $_SERVER['HTTP_HOST'].$value;
    }

}