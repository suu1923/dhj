<?php

namespace app\admin\model;

use think\Model;


class Equipment extends Model
{

    

    

    // 表名
    protected $name = 'equipment';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
//    protected $append = [
//        'status_text'
//    ];
    

    protected function getAdminIdAttr($value){
        return (Admin::get($value))['nickname'];
    }

    
    public function getStatusList()
    {
        return ['1' => __('Status 1'), '0' => __('Status 0'), '2' => __('Status 2'), '3' => __('Status 3')];
    }

    public function getModelStatusList(){
        return ['1' => "默认",'2'=>"自定义上传"];
    }


//    protected function getLogoImageAttr($value){
//        return $_SERVER['HTTP_HOST'].$value;
//    }
//    protected function getActiveImageAttr($value){
//        return $_SERVER['HTTP_HOST'].$value;
//    }
//    public function getStatusAttr($value){
//        $status = [1=>__("Open"),0=>__("Disable"),2=>__("Status 2"),3=>__("Status 3")];
//        return $status[$value];
//    }
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getLastInsID($sequence = null)
    {
        return parent::getLastInsID($sequence); // TODO: Change the autogenerated stub
    }


}
