<?php

namespace app\api\controller;

use app\api\model\MedRecordModel;
use think\Db;


class MedicineController extends Base
{

    /**
     * 列表所有
     */
    public function index()
    {
        //拿到user_id
        $uid = $this->getuid();
        if (empty($uid)) {
            die;
        }

        $med = new MedRecordModel();
        $res = $med->alias('a')
            ->join('hos_medicine b','b.id = a.medicine_id')

            ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期');
        }else{
            return $this->output_success(10001,[],'没有记录');
        }

    }
    /**
     * 提醒你该吃什么药了
     */
    public function remind()
    {
        //拿到user_id
        $uid = $this->getuid();
        //查询药品  名称，药品用量，服用周期
        $med = new MedRecordModel();
        $res = $med->alias('a')
                    ->join('hos_medicine b','a.medicine_id = b.id')
                    ->where(['user_id'=>$uid,'a.is_deleted'=>0])
                    ->field('dose,cycle,name')
                    ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期');
        }else{
            return $this->output_success(10001,[],'没有你该吃的药');
        }
    }

    /**
     * 展示服药记录
     */
    public function record()
    {
        //拿到user_id
        $uid = $this->getuid();
        if (empty($uid)) {
            return $this->output_error(10002,'请先登陆');
        }

        $start_time = input('start_time','','trim');
        $end_time = input('end_time','','trim');
        $where = [];
        if($start_time != 0 && $end_time != 0){
            $where['a.create_time'] = ['between',[$start_time,$end_time]];
        }elseif ($start_time != 0){
            $where['a.create_time'] = ['>=',$start_time];
        }elseif ($end_time != 0){
            $where['a.create_time'] = ['<=',$end_time];
        }

//        var_dump($uid);exit();
        $med = new MedRecordModel();
        $res = $med->alias('a')
            ->field('a.*,b.*,a.id as med_record_id,a.create_time as med_create_time')
            ->join('hos_medicine b','a.medicine_id = b.id')
            ->where(['user_id'=>$uid,'a.is_deleted'=>0])
            ->where($where)
            ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期,id是在删除提醒的时候给我的
            我要用它删除指定的提醒');
        }else{
            return $this->output_success(10001,[],'没有你该吃的药');
        }
    }


    /**
     * 服药详情
     */
    public function info()
    {
        //拿到user_id
        $uid = $this->getuid();
        //查询药品  名称，药品用量，服用周期
        $med = new MedRecordModel();
        $res = $med->alias('a')
            ->join('hos_medicine b','a.medicine_id = b.id')
            ->where(['user_id'=>$uid,'is_deleted'=>0])
            ->field('dose,cycle,name')
            ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期');
        }else{
            return $this->output_success(10001,[],'没有你该吃的药');
        }
    }
    /**
     * 软删除数据
     */
    public function delete()
    {
        //拿到user_id
        $uid = $this->getuid();

        $id = input('med_id',0,'intval');
        //软删除用药提醒
        $res = Db::name('med_record')
            ->where(['user_id'=>$uid,'id'=>$id])
            ->update(['is_deleted'=>1]);
//            ->update(['a.is_deleted'=>1]);

        if ($res){
            return $this->output_success(10011,$res,'用药删除成功');
        }else{
            return $this->output_success(10003,$res,'用药提醒已经删除');
        }
    }


}
