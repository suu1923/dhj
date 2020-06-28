<?php
/**
 * Created by PhpStorm.
 * User: Suu
 * Date: 2020/5/8
 * Time: 13:01
 */

namespace app\api\controller;


use app\admin\model\ChangeStatus;
use app\admin\model\Msg;
use app\application\extra\Code;
use app\common\controller\Api;
use app\common\model\Equipment;

class Dhj extends Common
{
    protected $noNeedLogin = ["*"];

    // 获取设备信息
    public function get_equipment_data(){
        if ($this->request->isPost()){
            $number = $this->request->post("number");
            if (empty($number) || $number == null) $this->error("请求错误");
            $result = (new Equipment())->where(["number" => $number])->field("id,name,activename,activeimage,activemsg,phone,address,status,modelstatus")->find();
            if ($result == null){
                $this->error("无效的设备ID",null,Code::QEUIPMENT_NOTFOUND);
            }
//            if ($result['status'] == 0) $this->error("机器已被锁定",null,Code::QEUIPMENT_DISABLE);
//            if ($result['status'] == 2) $this->error("机器在维护",null,Code::QEUIPMENT_MAINTAIN);
//            if ($result['status'] == 3) {
//                // 查找设备异常信息，关联到状态修改表里面
//                $errReason = (new ChangeStatus())->where(['eq_id'=>$result['id'],'state'=>$result['status']])->order('createtime desc')->limit(1)->value("reason");
//                $this->error("机器异常，原因：{$errReason}",null,Code::QEUIPMENT_ABNORMAL);
//            };
            if ($result['status'] != 1){
                switch ($result['status']){
                    case 0:
                        $errMsg = "机器已被锁定";
                        $code = Code::QEUIPMENT_DISABLE;
                        break;
                    case 2:
                        $errMsg = "机器在维护";
                        $code = Code::QEUIPMENT_MAINTAIN;
                        break;
                    case 3:
                        $errMsg = "机器异常";
                        $code = Code::QEUIPMENT_ABNORMAL;
                        break;
                    default:
                        $errMsg = "错误的异常信息";
                        $code = Code::QEUIPMENT_ABNORMAL;
                }
                $errReason = (new ChangeStatus())->where(['eq_id'=>$result['id'],'state'=>$result['status']])->order('createtime desc')->limit(1)->value("reason");
                $this->error("{$errMsg}，原因：{$errReason}",null,$code);
            }
            unset($result['id']);

            $this->success("ok",$result);
        }else{
            $this->error("网络错误",null,Code::NETWORK_ERROR);
        }
    }

    // 上传图片
        public function upload_res_img(){
        // 获取上传的机器ID，获取到文件
        if ($this->request->isPost()){
            $number = $this->request->post("number");
            $file = $this->upload("/uploads/client");
            // 添加到数据库
            $data['number'] = $number;
            $data['content'] = $file;
            $msg = new Msg();
            $msg->allowField(["content","number"])->save($data);
            if ($msg){
                // 获取到这个ID
                $this->success("ok",["link"=>"http://".$_SERVER['HTTP_HOST']."/page/dhj/gview/id/".$msg->id]);
            }else{
                $this->error("留言失败",null,Code::LEAVING_MSG_FAIL);
            }
        }else{
            $this->error("网络错误",null,Code::NETWORK_ERROR);
        }
    }
}