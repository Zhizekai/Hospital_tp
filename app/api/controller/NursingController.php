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
     * 护理列表
     * 需要登陆
     */
    public function index()
    {

        $this->check_power();


        $start_time = input('start_time',0,'intval');
        $end_time = input('end_time',0,'intval');



        $map = [];
        if($start_time != 0 && $end_time != 0){
            $map['create_time'] = ['between',[$start_time,$end_time]];
        }elseif ($start_time != 0){
            $map['create_time'] = ['>=',$start_time];
        }elseif ($end_time != 0){
            $map['create_time'] = ['>=',$end_time];
        }
        $map['a.is_deleted'] = 0;

        $res = Db::name('nursing')->alias('a')->join([['hos_user b','a.user_id = b.id']])->where('is_deleted',0)
            ->field('name,mobile,a.*')

            ->select();
        if (!empty($res)) {
            return $this->output_success(10010, $res, '这些都是护理记录');
        } else {
            return $this->output_error(11000, '护理记录查询失败');
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


        $this->check_power();


        $item   =  input('item','','trim');
        $record_time   =  input('record_time','','trim');
        $frequency  =  input('frequency','','trim');
        $times   =  input('times','','trim');
        $man   =  input('man','','trim');


        $nursing = new nursingmodel();
        $res = $nursing->validate(true)->insert([
            'user_id'=>$uid,
            'item'=>$item,
            'record_time'=>$record_time,
            'frequency' => $frequency,
            'times'  => $times,
            'man'   =>  $man,
            'create_time'>time()
        ]);

        if ($res){
            return $this->output_success(10011,[],'护理记录添加成功!');
        }else{
            return $this->output_error(10003,'护理记录添加失败!');
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
        $this->check_power();


        //护理记录字段的id
        $id = input('id',0,'intval');

        //用户的id
        $user_id = input('id',0,'intval');
        $item   =  input('item','','trim');
        $record_time   =  input('record_time','','trim');
        $frequency  =  input('frequency','','trim');
        $times   =  input('times','','trim');
        $man   =  input('man','','trim');


        //验证登陆
        $token = $this->check_sign();
        if(empty($token)){
            return $this->output_error(10002,'请先登录！');
        }


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
            return $this->output_error(10003,'护理记录修改失败!');
        }
    }


    /**
     * 护理删除
     */
    public function delete()
    {

        $this->check_power();



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
            return $this->output_error(10003,'护理记录删除失败!');
        }
    }

}
