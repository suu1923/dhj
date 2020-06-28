<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\ChangeStatus;
use app\admin\model\Customer;
use app\common\controller\Backend;
use fast\Random;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;


/**
 * 设备管理
 *
 * @icon fa fa-circle-o
 */
class Equipment extends Backend
{
    
    /**
     * Equipment模型对象
     * @var \app\admin\model\Equipment
     */
    protected $model = null;

    protected $userModel = null;

    protected $dataLimit;

    protected $modelValidate = true;

    protected $dataLimitField;

    protected $noNeedRight = ['*'];

    protected $noNeedLogin = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Equipment;
        $this->userModel =  model('Admin');
//        $this->view->assign("statusList", $this->model->getModelStatusList());
        $this->dataLimit = true;
        $this->dataLimitField = "adminid";
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $params['number'] = create_eq_number();


                $result_equipment = false;
                $result_user = false;
                Db::startTrans();
                try {
                    // 创建相应的角色
                    $user['username'] = $params['number'];
                    $user['email'] = "test".Random::alnum(3)."@".rand(10000,99999).".com";
                    $user['nickname'] = $params['name'];
                    $user['avatar'] = '/assets/img/avatar.png'; //设置新管理员默认头像。
                    $user['salt'] = Random::alnum();
                    $user['password'] = md5(md5("123456" ). $user['salt']);

                    $result_user = $this->userModel->validate('Admin.add')->save($user);
                    if ($result_user === false) {
                        $this->error($this->userModel->getError());
                    }
                    $group = $params['groupid']; // 所属组

                    $dataset[] = ['uid' => $this->userModel->id, 'group_id' => $group];

                    model('AuthGroupAccess')->saveAll($dataset);
                    // 创建角色、组End

                    // 机器表写入
                    // 是否采用模型验证
                    if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                        $params[$this->dataLimitField] = $this->userModel->id;
                    }
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result_equipment = $this->model->allowField(true)->save($params);


                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result_equipment !== false&& $result_user !== false) { //
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
            return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($params['modelstatus'] == 1){
                    unset($params['activeimage']);
                }

                if ($params['modelstatus'] == 2){
                    unset($params['activemsg']);
                    unset($params['phone']);
                    unset($params['address']);
                }

                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function customer_management(){
        $id = input("ids");
        $data = Customer::get(["eq_id"=>$id]);

        if ($this->request->isPost()){
            $params = $this->request->post("row/a");
            $isUpadte = $data ? true : false;

            if($isUpadte){
                $result = (new Customer())->isUpdate(true)->save($params,['eq_id'=>$id]);
            }else{
                $params['eq_id'] = $id;
                $result = (new Customer())->isUpdate(false)->save($params);
            }
            if ($result) $this->success(__("EditSuccess"));
            else $this->error(__("EditFail"));
        }
        // 有返回给前端
        $this->assign("data",$data);
        return $this->view->fetch();
    }

    // 禁用及解封  设备
    public function change_status($ids){
        $id = input("ids");
        $data = $this->model->where('id',$id)->field("number,status")->find();
        $realData = $this->model->where('id',$id)->field("number,status")->find()->getData("status");
        if ($realData != 1){
            $where['eq_id'] = $id;
            $where['state'] = (string)$realData;
            $reason = (new ChangeStatus())->where($where)->order('createtime desc')->limit(1)->value('reason');
            $reason = $reason ? $reason : "无";
            $this->assign("reason",$reason);
        }
        // 1 正常。0 禁用
        // 禁用设备 以及禁用账户
        if ($this->request->isPost()){
            $params = $this->request->post();
            if (!isset($params['type'])) $this->error("请选择状态");
            $change = [
                'operat_uid' => $this->auth->id,
                'eq_id'=>$id,
                'state' => $params['type'],
                'reason' => $params['reason'],
                'createtime' => time()
            ];
            // 设备
            $eq = [
                "id"=>$id,
                "status" => $params['type']
            ];
            // 管理员状态
            $userStatus = $params['type'] ? "normal" : "hidden";
            // 返回信息
            $resMsg = $params['type'] ? __("Open") : __("Disable");
            Db::startTrans();
            try{
                // 先写修改记录表
                (new ChangeStatus())->insert($change);
                // 改变管理员状态
                if ($params['type'] != 2){
                    (new Admin())->isUpdate(true)->save(['status'=>$userStatus],['username'=>$data['number']]);
                }
                // 设备
                $this->model->isUpdate(true)->save($eq);
                Db::commit();
                $this->success("{$resMsg}".__("Success"));
            }catch (\think\Exception $e){
                Db::rollback();
                $this->error("{$resMsg}".__("Fail"),"",$e->getMessage());
            }

        }

        $this->assign("status",$realData);
        return $this->view->fetch();

    }
}
