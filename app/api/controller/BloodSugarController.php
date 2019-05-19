<?php
/**
 * Created by PhpStorm.
 * User: 17313
 * Date: 2019/4/11
 * Time: 20:19
 */

namespace app\api\controller;

use app\api\model\BloodSugarModel;
use think\Db;
class BloodSugarController extends Base
{
    /**
     * 血糖列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        //验证登陆
        $uid = $this->getuid();
        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }
//        $uid = 3;
        $start_time = strtotime(input('start_time', '', 'trim'));
        $end_time   = strtotime(input('end_time', '', 'trim'));

        $map = [];
        if ($start_time != 0 && $end_time != 0) {
            $map['amount_time'] = ['between', [$start_time, $end_time]];
        } elseif ($start_time != 0) {
            $map['amount_time'] = ['>=', $start_time];
        } elseif ($end_time != 0) {
            $map['amount_time'] = ['>=', $end_time];
        }

        $bloodsugar_model = new BloodSugarModel();
        $search = $bloodsugar_model
            ->where(['user_id'=>$uid,'deleted_time'=>0])//查询没有被删除的字段
            ->where($map)//按照时间区间查询数据
            ->order('amount_time')
            ->field('from_unixtime(create_time, \'%Y-%m-%d  %H:%i:%S\') as create_time,from_unixtime(amount_time, \'%Y-%m-%d  %H:%i:%S\') as amount_time,type,blood_sugar,user_id,id')
            ->select();
        foreach ($search as $item){
            $item->situation = $bloodsugar_model->getStatusAttr($item['blood_sugar'],$item['type']);
            $item['type_num'] = $item['type'];
            $item->type = $bloodsugar_model->getType($item['type']);

        }

        if ($search){
            return $this->output_success(10000,$search,'读取血糖信息成功');
        } else {
            return $this->output_error(10010,'读取血糖信息失败');
        }
    }


    /**
     * 添加血糖
     * @return array
     */
    public function add(){
        $uid = $this->getuid();
        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }
//        $uid = 3;
        $type = input('type',0,'intval');
        $blood_sugar = input('blood_sugar','','trim');
        $create_time = time();
        $amount_time = input('amount_time','','trim');
        $amount_time = strtotime($amount_time); //转成时间戳


        if (!in_array($type,[1,2,3,4,5,6,7,8])){
            return $this->output_error(10001, '请填写正确的type类型!');
        }
        if(empty($blood_sugar)){
            return $this->output_error(10001, '请输入血糖值!');
        }
        if($amount_time ==0 ){
            $amount_time = time();
        }


        $bloodsugar_model = new BloodSugarModel();
        $res = $bloodsugar_model
            ->save([
                'type'=>$type,
                'blood_sugar'=>$blood_sugar,
                'create_time'=>$create_time,
                'amount_time'=>$amount_time,
                'user_id'=>$uid
            ]);
        if ($res){
            return $this->output_success(10000,[],'血糖信息添加成功');
        } else {
            return $this->output_error(10010,'血糖信息添加失败');
        }
    }

    /**
     * 血糖删除
     * @return array
     */
    public function delete()
    {
        //验证登陆
        $uid = $this->getuid();
        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }

        $blood_sugar_id = input('blood_sugar_id','','intval');
//        $uid = 1;
        $blood_sugar_model = new BloodSugarModel();
        $res = $blood_sugar_model->save([
            'deleted_time'=>time()
        ],['user_id'=>$uid,'id'=>$blood_sugar_id]);

        if ($res){
            return $this->output_success(10000,[],'血糖信息删除成功');
        } else {
            return $this->dang_output_error(10010,[],'血糖信息删除失败');
        }


    }



    public function edit()
    {
        //验证登陆
        $uid = $this->getuid();
        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }
//        $uid = 3;
        $type = input('type',0,'intval');
        $blood_sugar = input('blood_sugar','','trim');
        $blood_sugar_id = input('blood_sugar_id','','intval');
        $create_time = time();
        $amount_time = input('amount_time',0,'trim');
        $amount_time = strtotime($amount_time);


        if (!in_array($type,[1,2,3,4,5,6,7,8])){
            return $this->output_error(10001, '请填写正确的type类型!');
        }
        if(empty($blood_sugar)){
            return $this->output_error(10001, '请输入血糖值!');
        }
        if($amount_time ==0 ){
            $amount_time = time();
        }

        /*更新数据*/
        $blood_sugar_model = new BloodSugarModel();
        $res = $blood_sugar_model
            ->save([
                'type'=>$type,
                'blood_sugar'=>$blood_sugar,
                'create_time'=>$create_time,
                'amount_time'=>$amount_time,
                'user_id'=>$uid
            ],['user_id'=>$uid,'id'=>$blood_sugar_id]);
        if ($res){
            return $this->output_success(10000,[],'血糖信息编辑成功');
        } else {
            return $this->dang_output_error(10010,[],'血糖信息编辑就失败');
        }
    }

}