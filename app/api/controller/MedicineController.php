<?php

namespace app\api\controller;

use app\api\model\MedRecordModel;
use think\console\command\make\Model;
use think\Db;
use think\Request;
use think\Session;
use think\Cache;
use token\Token;

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
            return $this->output_error(10002,'请先登陆');
        }

        $med = new MedRecordModel();
        $res = $med->alias('a')
            ->join('hos_medicine b','a.medicine_id = b.id')
            ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期');
        }else{
            return $this->output_error(10001,'没有记录');
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
                    ->where(['user_id'=>$uid,'is_deleted'=>0])
                    ->field('dose,cycle,name')
                    ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期');
        }else{
            return $this->output_error(10001,'没有你该吃的药');
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

        $med = new MedRecordModel();
        $res = $med->alias('a')
            ->join('hos_medicine b','a.medicine_id = b.id')
            ->where(['user_id'=>$uid,'is_deleted'=>0])
            ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期,id是在删除提醒的时候给我的
            我要用它删除指定的提醒');
        }else{
            return $this->output_error(10001,'没有你该吃的药');
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
            return $this->output_error(10001,'没有你该吃的药');
        }
    }
    /**
     * 软删除数据
     * @return string
     */
    public function delete()
    {
        //拿到user_id
        $uid = $this->getuid();

        $id = input('med_id',0,'intval');
        //软删除用药提醒
        $med = new MedRecordModel();
        $res = $med->alias('a')
            ->join('hos_medicine b','a.medicine_id = b.id')
            ->where(['user_id'=>$uid,'is_deleted'=>0,'id'=>$id])
            ->update(1,'is_deleted');

        if ($res){
            return $this->output_success(10011,[],'用药删除成功');
        }else{
            return $this->output_error(10003,'用药提醒删除失败');
        }
    }


    /**
     * 添加服药记录
     * @return string
     */
    public function insert()
    {
        $records_name = input('records_name','');
        $time = input('time','');
        $user_id = self::get_user_id();

        if (empty($records_name)&&empty($time)) {
            return '请输入药品名字和服用时间';
        }

        $data = [
            'records_name' => $records_name,
            'time' => $time,
            'user_id' => $user_id,
        ];

        $result = Db::table('eat_records')->insert($data);

        //显示结果
        self::outcome($result);

    }
}
