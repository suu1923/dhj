<?php/** * Created by PhpStorm. * User: a1538_000 * Date: 2020/5/31 * Time: 15:40 */namespace app\page\controller;use app\common\controller\Frontend;use think\Db;class Dhj extends Frontend{    protected $noNeedLogin = '*';    protected $noNeedRight = '*';    protected $layout = '';    public function gview($id=''){        if (empty($id)) return "<h1>页面错误</h1>";        // 获取到图片地址        $res = Db::table("p_msg")->where("id",$id)->field("content,number")->find();        if ($res||$res != null){            $res['title'] = Db::table("p_equipment")->where("number",$res['number'])->value("activename");        }        $this->assign("data",$res);        return $this->view->fetch();    }}