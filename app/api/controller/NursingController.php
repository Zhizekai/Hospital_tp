<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/22
 * Time: 10:23
 */

namespace app\api\controller;


use think\Db;
use app\api\model\NursingModel as nursingmodel;

class NursingController extends Base
{

    /**
     * 测试方法
     */
    public function zzk()
    {

        $token = input('token');
        $ii = [4234,1414,1414,2352,'sefsf'];
        return $ii;
    }

    /**
     * 护理记录列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {

//        //检验管理员是否登陆
//        $token = $this->check_sign();
//
//        //检验管理员是否有操作此模块的权限
//        if (!$this->check_power($token))
//        {
//            return $this->output_error(404,'无权限');
//        };

        $mobile = input('mobile','','trim');
        $item = input('item','','trim');

        $where = [];
        $where['mobile'] = ['like','%'.$mobile.'%'];
        $where['item'] = ['like','%'.$item.'%'];
        $where['a.is_deleted'] = 0;

        $res = Db::name('nursing')->alias('a')->join([['hos_user b','a.user_id = b.id']])
            ->where($where)
            ->field('name,mobile,a.*')
            ->select();
        if (!empty($res)) {
            return $this->output_success(10010, $res, '这些都是护理记录');
        } else {
            return $this->output_success(11000, [],'护理记录查询失败');
        }
    }

    /**
     * 增加护理记录
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };

        $item   =  input('item','','trim');
        $record_time   =  input('record_time','','trim');
        $frequency  =  input('frequency','','trim');
        $times   =  input('times','','trim');
        $man   =  input('man','','trim');

        $mobile = input('mobile','','trim');
        $name = input('name','','trim');
        $where['name'] = $name;
        $where['name'] = $mobile;
        $where['status'] = 1;

        $uid = Db::name('user')->where($where)->value('id');

        if  (empty($uid)) {
            return $this->output_success('500',[],'压根就没有这个用户，这明显是你输错了');
        }


        $nursing = new nursingmodel();
        $res = $nursing->validate(true)->insert([
            'user_id'=>$uid,
            'item'=>$item,
            'record_time'=>$record_time,
            'frequency' => $frequency,
            'times'  => $times,
            'man'   =>  $man,
        ]);

        if ($res){
            return $this->output_success(10011,[],'护理记录添加成功!');
        }else{
            return $this->output_success(10003,[],'护理记录添加失败!');
        }


    }


    /**
     * 更改护理记录
     * 需要登陆
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function update()
    {
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };


        //护理记录字段的id
        $id = input('id',0,'intval');

        //用户的id
        $user_id = input('id',0,'intval');
        $item   =  input('item','','trim');
        $record_time   =  input('record_time','','trim');
        $frequency  =  input('frequency','','trim');
        $times   =  input('times','','trim');
        $man   =  input('man','','trim');

        $nursing = new nursingmodel();
        $res = $nursing->validate(true)->where(['id'=>$id, 'is_deleted'=>0])->update([
            'user_id'=>$user_id,
            'item' => $item,
            'record_time' => $record_time,
            'frequency'  => $frequency,
            'times'  => $times,
            'man'    => $man
        ]);

        if ($res){
            return $this->output_success(10011,[],'护理记录修改成功!');
        }else{
            return $this->output_success(10003,[],'护理记录修改失败!');
        }
    }


    /**
     * 护理删除
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delete()
    {

        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };

        //护理记录字段的id
        $id = input('id',0,'trim');

        if (empty($id)) {
            return $this->output_error(500,'护理字段不能为空');
        }

        $nursing = new nursingmodel();
        $res = $nursing->where(['id'=>$id])->update([
            'is_deleted'=>1
        ]);

        if ($res){
            return $this->output_success(10011,[],'护理记录删除成功!');
        }else{
            return $this->output_success(10003,[],'护理记录删除失败!');
        }
    }

}
