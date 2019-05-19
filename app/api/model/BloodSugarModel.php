<?php
/**
 * Created by PhpStorm.
 * User: 17313
 * Date: 2019/4/11
 * Time: 20:43
 */

namespace app\api\model;


use think\Model;

class BloodSugarModel extends Model
{
    public function getStatusAttr($value,$type)
    {
//        空腹4.4-7.0mmol/l;餐后2h<10mmol/l;HbA1c(糖化血红蛋白）<7%
        /*空腹*/
        if ($type ==1){
            if ($value>7.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*早餐后*/
        if ($type ==2){
            if ($value>10.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*午餐前*/
        if ($type ==3){
            if ($value>7.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*午餐后*/
        if ($type ==4){
            if ($value>10.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*晚餐前*/
        if ($type ==5){
            if ($value>7.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*晚餐后*/
        if ($type ==6){
            if ($value>10.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*睡前*/
        if ($type ==7){
            if ($value>7.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
        /*未知*/
        if ($type ==8){
            if ($value>7.0){
                return '高血糖';
            }elseif ($value<4.4){
                return '低血糖';
            }else{
                return '达标';
            }
        }
    }

//    1: 空腹  2：早餐后  3：午餐前  4：午餐后  5：晚餐前  6：晚餐后  7：睡前 8：未知
    public function getType($value)
    {
        $status = [1=>'空腹',2=>'早餐后2h',3=>'午餐前',4=>'午餐后2h',5=>'晚餐前',6=>'晚餐后2h',7=>'睡前',8=>'未知'];
        return $status[$value];
    }
}