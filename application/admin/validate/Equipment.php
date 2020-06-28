<?php

namespace app\admin\validate;

use think\Validate;

class Equipment extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'activename'=>'max:9'
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'activename'=>'活动名最多9个字符！'
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['activename'],
        'edit' => ['activename'],
    ];
    
}
