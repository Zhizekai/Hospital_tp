<?php

namespace app\api\controller;

use app\api\model\MedRecordModel;
use think\console\command\make\Model;
use think\Db;
use think\Request;
use think\Session;
use think\Cache;

class MedicineController extends Base
{
    /**
     * 列表展示提醒你该吃什么药了
     */
    public function index()
    {
        //拿到user_id
        $uid = $this->getuid();
        //查询药品  名称，药品用量，服用周期
        $med = new MedRecordModel();
        $res = $med->alias('a')
                    ->join('hos_medicine b','a.medicine_id = b.id')
                    ->where('user_id',$uid)
                    ->field('dose,cycle,name')
                    ->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'name药品名称，dose药品用量，cycle服药周期');
        }else{
            return $this->output_error(10001,'没有你该吃的药');
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

    /**
     * 软删除数据
     * @return string
     */
    public function delete()
    {
        $user_id = self::get_user_id();

        $result = Db::table('eat_records')->where('user_id',$user_id)->setField('is_deleted',1);

        self::outcome($result);
    }

    /**
     * 展示你吃过哪些药了
     */
    public function show()
    {
        $user_id = self::get_user_id();

        $result = Db::table('eat_records')->where('user_id',$user_id)->select();

        echo json_encode($result);


    }
    public function b()
    {
        $data = cache::get('name');
        $dd = cache::get('dd');
        var_dump($data);
        var_dump($dd);

        $dd = M('users');

    }

}
