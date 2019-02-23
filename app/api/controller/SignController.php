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
     * 体征列表接口
     */
    public function index(){
        $start_time = input('start_time',0,'intval');
        $end_time = input('end_time',0,'intval');

        $where = [];
        if($start_time != 0 && $end_time != 0){
            $where['create_time'] = ['>=',$start_time];
            $where['create_time'] = ['<=',$end_time];
        }elseif ($start_time != 0){
            $where['create_time'] = ['>=',$start_time];
        }elseif ($end_time != 0){
            $where['create_time'] = ['>=',$end_time];
        }

        $res = Db::name('sign')->where($where)->select();

        if (!empty($res)){
            return $this->output_success(10010,$res,'体征列表获取成功！');
        }else{
            return $this->output_error(10001,'体征列表获取失败!');
        }
    }


    /**
     * 体征添加
     * @top_pressure  String 舒张压
     * @buttom_pressure String 收缩压
     * @heart_rate int 心率
     * return json
     */

    public function add(){
        $top_pressure = input('top_pressure','','trim');
        $buttom_pressure = input('buttom_pressure','','trim');
        $heart_rate = input('heart_rate',0,'intval');

        $token = $this->check_sign();
        if(!empty($token)){
            $uid = Token::get_user_id($token);
        }else{
            return $this->output_error(10002,'请先登录！');
        }

        $sign = new SignModel();
        $res = $sign->validate(true)->insert([
            'user_id'=>$uid,
            'top_pressure'=>$top_pressure,
            'buttom_pressure'=>$buttom_pressure,
            'heart_rate'=>$heart_rate,
            'create_time'=>time()]);

        if ($res){
            return $this->output_success(10011,[],'体征添加成功!');
        }else{
            return $this->output_error(10003,'体征添加失败!');
        }
    }

    /**
     * 体征修改
     * @top_pressure  String 舒张压
     * @buttom_pressure String 收缩压
     * @heart_rate int 心率
     * return json
     */
    public function edit(){
        $token = $this->check_sign();
        $id = input('id',0,'intval');
        $top_pressure = input('top_pressure','','trim');
        $buttom_pressure = input('buttom_pressure','','trim');
        $heart_rate = input('heart_rate',0,'intval');
        $create_time = time();


        if ($id ==0 ){
            return $this->output_error(10003,'请传入ID');
        }
        if(!empty($token)){
            $uid = Token::get_user_id($token);
        }else{
            return $this->output_error(10002,'请先登录！');
        }

        $sign = new SignModel();
        $res = $sign->validate(true)->where(['id'=>$id])->update(['user_id'=>$uid,'top_pressure'=>$top_pressure,'buttom_pressure'=>$buttom_pressure,'heart_rate'=>$heart_rate,'create_time'=>$create_time]);

        if ($res){
            return $this->output_success(10011,[],'体征更新成功!');
        }else{
            return $this->output_error(10003,'体征更新失败!');
        }
    }

    /**
     * 体征删除
     */
    public function delete(){
        $id = input('id',0,'intval');
        $token = $this->check_sign();

        if (empty($token)){
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