<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/22
 * Time: 11:19
 */

namespace app\api\controller;


use app\api\model\MedicineSaleModel;
use think\Db;

class MedicineSaleController extends Base
{

    /**
     * 卖药的列表
     */
    public function index()
    {

        $this->check_power();


        $start_time = input('start_time', '', 'intval');
        $end_time = input('end_time', '', 'intval');

        $map = [];
        if ($start_time != 0 && $end_time != 0) {
            $map['create_time'] = ['between', [$start_time, $end_time]];
        } elseif ($start_time != 0) {
            $map['create_time'] = ['>=', $start_time];
        } elseif ($end_time != 0) {
            $map['create_time'] = ['>=', $end_time];
        }
        $map['b.is_deleted'] = 0;

        $res = Db::name('med_sale')->alias('a')
            ->join(['hos_medicine b', 'a.medicine_id = b.id'])
            ->where($map)->field('num,name,price,medicine_id')
            ->select();


        if (!empty($res)) {
            return $this->output_success(10010, $res, 'name药品名称，price价格，num数量，medicine_id药品id');
        } else {
            return $this->output_error(10001, '体征列表获取失败!');
        }

    }

    /**
     * 增加销售记录
     */
    public function add()
    {

        $this->check_power();

        $medicine_id = input('medicine_id', 0, 'trim');
        $num = input('num', 0, 'trim');

        $med_sale = new MedicineSaleModel();
        $res = $med_sale->validate(true)->insert([
            'medicine_id' => $medicine_id,
            'num'         => $num,
            'create_time' => time(),
        ]);

        if ($res){
            return $this->output_success(10011,[],'销售记录成功!');
        }else{
            return $this->output_error(10003,'销售记录添加失败!');
        }

    }
}