<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/16
 * Time: 15:52
 */

namespace app\api\controller;


use app\api\model\SignModel;
use think\Db;
use token\Token;

class SignController extends Base
{


    /**
     * 体征列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $start_time = input('start_time',0,'intval');
        $end_time = input('end_time',0,'intval');

        $uid = $this->getuid();

        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }

        $where = [];
        if($start_time != 0 && $end_time != 0){
            $where['create_time'] = ['>=',$start_time];
            $where['create_time'] = ['<=',$end_time];
        }elseif ($start_time != 0){
            $where['create_time'] = ['>=',$start_time];
        }elseif ($end_time != 0){
            $where['create_time'] = ['>=',$end_time];
        }

        $res = Db::name('sign')->where($where)->where('delete_time',0)->where(['user_id'=>$uid])->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'体征列表获取成功！');
        }else{
            return $this->output_error(10001,'体征列表获取失败!');
        }
    }


    /**
     * 体征添加
     * @top_pressure  String 舒张压
     * @bottom_pressure String 收缩压
     * @heart_rate int 心率
     * return json
     */

    public function add(){
        $top_pressure = input('top_pressure','','trim');
        $buttom_pressure = input('bottom_pressure','','trim');
        $heart_rate = input('heart_rate',0,'intval');
        $create_time = input('create_time','','trim');


        $uid = $this->getuid();

        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }

        $res = Db::name('sign')->insert([
            'user_id'=>$uid,
            'top_pressure'=>$top_pressure,
            'bottom_pressure'=>$buttom_pressure,
            'heart_rate'=>$heart_rate,
            'create_time' => $create_time
        ]);

        if ($res){
            return $this->output_success(10011,[],'体征添加成功!');
        }else{
            return $this->output_success(10003,[],'你没把数据加进去');
        }
    }

    /**
     * 体征修改
     * @top_pressure  String 舒张压
     * @bottom_pressure String 收缩压
     * @heart_rate int 心率
     * return json
     */
    public function edit(){

        $id = input('id',0,'intval');
        $top_pressure = input('top_pressure','','trim');
        $buttom_pressure = input('bottom_pressure','','trim');
        $heart_rate = input('heart_rate',0,'intval');
        $create_time = input('create_time','','trim');



        if ($id ==0 ){
            return $this->output_error(10003,'你不给我体征的id我哪知道我要改哪条体征');
        }
        $uid = $this->getuid();

        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }


        $res = Db::name('sign')->where(['id'=>$id])->update([

            'top_pressure'=>$top_pressure,
            'bottom_pressure'=>$buttom_pressure,
            'heart_rate'=>$heart_rate,
            'create_time' => $create_time

        ]);

        if ($res){
            return $this->output_success(10011,[],'体征更新成功!');
        }else{
            return $this->output_success(10003,[],'体征没有写进数据库!');
        }
    }

    /**
     * 体征删除
     */
    public function delete(){
        $id = input('id',0,'intval');
        $uid = $this->getuid();

        if(empty($uid)){
            return $this->output_error(10002,'请先登录！');
        }

        $res = Db::name('sign')->where('id',$id)->update(['delete_time'=>time()]);

        if ($res){
            return $this->output_success(10011,[],'体征删除成功!');
        }else{
            return $this->output_error(10003,'体征删除失败!');
        }
    }







}