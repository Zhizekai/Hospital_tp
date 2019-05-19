<?php
/**
 * Created by PhpStorm.
 * User: cl
 * Date: 2019/3/8
 * Time: 19:33
 */

namespace app\api\controller;
use think\Db;

class BackOrderController extends Base
{


    public function zzk()
    {
        $res = Db::view('user','id,name,mobile')
            ->view('order','id,create_time,status,types','order.user_id = user.id')
            ->select()->toArray();

        foreach($res as $key => $val) {

            $res[$key]['types'] = (array)json_decode($val['types']);

        }

        if (!empty($res)){
            return $this->output_success(20001,$res,'预约列表获取成功！');
        }else{
            return $this->output_error(20002,'预约列表获取失败!');
        }
    }



    /**
     * 预约列表接口
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */

//    0所有 1通过 2拒绝 3未审核
    public function index(){

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

        $types = input('types','','trim');
        $status = input('status',0,'intval');
        $mobile = input('mobile','','trim');
        $name = input('name','','trim');

        $where = [];
        if (!empty($types)){
            $where['types'] = $types;
        }elseif ($status != 0 ){
            $where['status'] = $status;
        }elseif (!empty($mobile)){
            $where['mobile'] = $mobile;
        }elseif (!empty($name)){
            $where['name'] = $name;
        }

        $uid = $this->getuid();

        if (empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }

        $res = Db::view('user','id,name,mobile')
            ->view('order','id,create_time,status,types','order.user_id = user.id')
            ->where($where)
            ->select();

        if (!empty($res)){
            return $this->output_success(20001,$res,'预约列表获取成功！');
        }else{
            return $this->output_error(20002,'预约列表获取失败!');
        }
    }

    /**
     * 预约审核接口
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */

    public function action(){


        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

        $id = input('id',0,'intval');
        $status = input('status',0,'intval');

        $uid = $this->getuid();

        if (empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }

//        $order = new OrderModel();
//        $res = $order->validate(true)
        $res = Db::name('order')
            ->where(['id'=>$id])
            ->update(['status'=>$status]);

        if ($res){
            return $this->output_success(20003,[],'预约处理成功!');
        }else{
            return $this->output_error(20004,'预约处理失败!');
        }
    }
}