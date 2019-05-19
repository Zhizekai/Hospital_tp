<?php
///**
// * Created by PhpStorm.
// * User: cl
// * Date: 2019/3/8
// * Time: 19:32
// */
//
//namespace app\api\controller;
//
//
//class BackmedController
//{
//
//}

/**
 * Created by PhpStorm.
 * User: cl
 * Date: 2019/2/17
 * Time: 15:24
 */

namespace app\api\controller;

use think\Db;

class BackMedicineController extends Base
{
    /**
     *药品调度列表
     */

    public function index()
    {

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

        $mobile = input('mobile', 0, 'intval');
        $name = input('name', '', 'trim');

        $where = [];
        if (!empty($mobile)) {
            $where['mobile'] = $mobile;
        } elseif (!empty($name)) {
            $where['name'] = $name;
        }

        $uid = $this->getuid();

        if (empty($uid)) {
            return $this->output_error(10002, '请先登录！');
        }

//        $res = Db::view('user', 'id,name,mobile')
//            ->view('med_record', 'id,dose,cycle,is_deleted', 'med_record.user_id = user.id')
//            ->view('medicine', 'id,name,attention', 'medicine.id = med_record.medicine_id ')
//            ->where($where)
//            ->where('is_deleted', 0)
//            //->fetchSql()
//            ->select();
        $res = Db::name('user')->alias('u')
            ->join('med_record r','r.user_id = u.id')
            ->join('medicine m','m.id = r.medicine_id')
            ->where($where)
            ->where(['r.is_deleted'=>0])
            ->field('u.id as uid,u.name,u.mobile,r.id,r.dose,r.cycle,r.num,m.id as mid,m.name as mname,m.attention')
            ->select();


        if (!empty($res)) {
            return $this->output_success(20001, $res, '药品调度列表获取成功');
        } else {
            return $this->output_error(20002, '药品调度列表获取失败');
        }
    }


    /**
     *所有药品
     */

    public function type(){


        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

        $all_med = Db::name('medicine')
            ->field('name')
            ->select();

        if (!empty($all_med)) {
            return $this->output_success(20001, $all_med, '药品获取成功');
        } else {
            return $this->output_error(20002, '药品获取失败');
        }
    }


    /**
     *药品售药列表
     */


    public function medshow()
    {


        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========
//搜索药品名称
        $name = input('name', '', 'trim');
//时间戳确定当时时间//找到上一个月时间
        $last_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
        //上上月
        $two_last_time = date('Y-m-01 00:00:00',strtotime('-2 month'));

        $where = [];
        if (!empty($name)) {
            $where['m.name'] = $name;
        }

        $uid = $this->getuid();

        if (empty($uid)) {
            return $this->output_error(10002, '请先登录！');
        }
        $where['s.create_time'] = $last_time;
        $last_month_count = Db::name('medicine')->alias('m')
            ->join('med_sale s', 'm.id = s.medicine_id')
            ->where($where)
            ->group('s.medicine_id')
            ->field('sum(num) as total,m.name,s.medicine_id')
            ->select()->toArray();

        $last_arr = [];
        foreach ($last_month_count as $item){
            $last_arr[$item['medicine_id']]=$item;
        }

        $where['s.create_time'] = $two_last_time;
        $two_month_count = Db::name('medicine')->alias('m')
            ->join('med_sale s', 'm.id = s.medicine_id')
            ->where($where)
            ->group('s.medicine_id')
            ->field('sum(num) as total,s.medicine_id')
            ->select()->toArray();
        $two_arr = [];
        foreach ($two_month_count as $item){
            $two_arr[$item['medicine_id']]=$item;
        }
        $sale_list = [];
        foreach($last_arr as $key=>$item){
            if (!isset($two_arr[$key])){
                $last_arr[$key]['ratio'] = 1;
            }else{
                $last_arr[$key]['ratio'] = $item['total']-$two_arr[$key]['total'];
            }
            $last_arr[$key]['last_time'] = $last_time;
            array_push($sale_list,$last_arr[$key]);

        }


        if ($last_month_count) {
            return $this->output_success(20009, $sale_list, '销售列表获取成功');
        } else {
            return $this->output_error(20010, '销售列表获取失败');
        }
    }

    /**
     *药品调度添加列表
     */

    public function add()
    {

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

//        user表里数据，通过这两条数据查询到user_id，然后添加
        $name = input('name', '', 'trim');
        $mobile = input('mobile', 0, 'intval');

//        药品表里数据,通过该数据查询到medicine_id，然后添加
        $medicine = input('medicine', '', 'trim');

//        药品调度表，添加记录
        $dose = input('dose', '', 'trim');
        $cycle = input('cycle', '', 'trim');
        $num = input('num', 0, 'intval');
//        获取当前时间
        $create_time = date('Y-m-01 00:00:00', time());

        $uid = $this->getuid();

        if (empty($uid)) {
            return $this->output_error(10002, '请先登录！');
        }

//        查询user_id,medicine_id

        $user_id = Db::view('user', 'id')
            ->where(['name' => $name, 'mobile' => $mobile])
            ->value('id');


        if( empty($user_id)){
            return $this->output_error(20016, '无此用户');
        }

        $medicine_id = Db::view('medicine', 'id')
            ->where(['name' => $medicine])
            ->value('id');

        $num_update = Db::view('med_sale', 'create_time,num')
            ->where(['create_time' =>  $create_time, 'medicine_id' => $medicine_id])
            ->value('num');

        //        添加记录至med_record表

//        $med = new MedicineModel();
//        $res = $med->validate(true)
        $res = Db::name('med_record')
            ->insert(['user_id' => $user_id, 'medicine_id' => $medicine_id, 'dose' => $dose, 'cycle' => $cycle, 'num' => $num]);

        if ($res) {

            //            将录入的盒数放进药品销售表
            //        获取当前时间，从表中查询该时间，存在，则进行相加更新，不存在，则新增

            if (!empty($num_update)) {
                $res = Db::name('med_sale')
                    ->where(['create_time' => $create_time])
                    ->update(['num' => $num_update[0][1] + $num]);
                if ($res) {
                    return $this->output_success(20005, [], '药品调度记录添加成功,该月份售药记录更新成功');
                } else {
                    return $this->output_error(20006, '药品调度记录添加成功,该月份售药记录更新失败');
                }
            } else {
                $res = Db::name('med_sale')
                    ->insert(['create_time' =>  $create_time, 'medicine_id' => $medicine_id, 'num' => $num]);
                if ($res) {
                    return $this->output_success(20007, [], '药品调度记录添加成功,新月份售药记录添加成功');
                } else {
                    return $this->output_error(20008, '药品调度记录添加成功,新月份售药记录添加失败');
                }
            }

        } else {
            return $this->output_error(20004, '药品调度记录添加失败');
        }
    }


    /**
     *药品调度修改接口
     */


    public function update()
    {

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

//        user表里数据，通过这两条数据查询到user_id，然后添加
        $name = input('name', '', 'trim');
        $mobile = input('mobile', '', 'trim');

//        药品表里数据,通过该数据查询到medicine_id，然后添加
        $medicine = input('medicine', '', 'trim');

//        药品调度表，修改记录
        $id = input('id', 0, 'intval');
        $dose = input('dose', '', 'trim');
        $cycle = input('cycle', '', 'trim');
        $num = input('num', '', 'trim');


        //        获取当前时间取整
        $create_time = date('Y-m-01 00:00:00', time());

        $uid = $this->getuid();

        if (empty($uid)) {
            return $this->output_error(10002, '请先登录！');
        }

//        查询user_id,medicine_id

        $user_id = Db::view('user', 'id')
            ->where(['name' => $name, 'mobile' => $mobile])
            ->value('id');
        $medicine_id = Db::view('medicine', 'id')
            ->where(['name' => $medicine])
            ->value('id');
        //        修改记录至med_record表
        $before_num = Db::view('med_record', 'medicine_id,num')
            ->where(['id' => $id])
            ->select()
            ->toArray();
//        var_dump($before_num);
//        exit;

        $before_sale_num = Db::view('med_sale', 'create_time,num')
            ->where(['create_time' =>$create_time, 'medicine_id' => $before_num[0]["medicine_id"]])
            ->select()
            ->toArray();

        Db::name('med_record')
            ->where(['id' => $id])
            ->update(['user_id' => $user_id, 'medicine_id' => $medicine_id, 'dose' => $dose, 'cycle' => $cycle, 'num' => $num]);



//            先删除上一条数据
        $new_num = Db::name('med_sale')
            ->where(['create_time' => $create_time, 'medicine_id' => $before_num[0]["medicine_id"]])
            ->update(['num' => $before_sale_num[0]["num"] - $before_num[0]["num"]]);

        if ($new_num) {
            //            再新增新的数据  获取当前时间，从表中查询该时间，存在，则进行相加更新，不存在，则新增
            $num_update = Db::view('med_sale', 'create_time,num')
                ->where(['create_time' => $create_time, 'medicine_id' => $medicine_id])
                ->select();

            if (!empty($num_update)) {
                $res = Db::name('med_sale')
                    ->where('create_time',$create_time)
                    ->update(['num' => $num_update[0]["num"] + $num]);
                if ($res) {
                    return $this->output_success(20011, [], '药品调度记录修改成功,该月份售药记录更新成功');
                } else {
                    return $this->output_error(20012, '药品调度记录修改成功,该月份售药记录更新失败');
                }
            } else {
                $res = Db::name('med_sale')
                    ->insert(['create_time' => $create_time, 'medicine_id' => $medicine_id, 'num' => $num]);
                if ($res) {
                    return $this->output_success(20013, [], '药品调度修改添加成功,新月份售药记录添加成功');
                } else {
                    return $this->output_error(20014, '药品调度修改添加成功,新月份售药记录添加失败');
                }
            }
        }


    }


    /**
     *药品调度删除接口
     */

    public function delete()
    {

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========
        $id = input('id', 0, 'intval');
        $create_time = date('Y-m-01 00:00:00', time());

        $uid = $this->getuid();

        if (empty($uid)) {
            return $this->output_error(10002, '请先登录！');
        }

        Db::name('med_record')
            ->where(['id' => $id])
            ->update(['is_deleted' => 1]);

            $before_num = Db::view('med_record', 'medicine_id,num')
                ->where(['id' => $id])
                ->select();
            $before_sale_num = Db::view('med_sale', 'create_time,num')
                ->where(['create_time' => $create_time, 'medicine_id' => $before_num[0]['medicine_id']])
                ->select();

            $new_num = Db::name('med_sale')
                ->where(['medicine_id' => $before_num[0]['medicine_id']])
                ->update(['num' => $before_sale_num[0]['num'] - $before_num[0]['num']]);
            if ($new_num) {
                return $this->output_success(20016, [], '药品调度记录删除成功');
            } else {
                return $this->output_error(20017, '药品调度记录删除失败');
            }

    }

    /**
     *药品添加接口
     */

    public function add_med()
    {

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========
        $name = input('name');
        $price = input('price');
        $attention = input('attention');

        $res = Db::name('medicine')
            ->insert(['name' => $name, 'price' => $price, 'attention' => $attention]);
        if ($res) {
            return $this->output_success(20018, [], '药品添加成功');
        } else {
            return $this->output_error(20019, '药品添加失败');
        }
    }

    /**
     *某药品销售情况
     */

    public function all()
    {

        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========
        $id = input('id');

        $res = Db::name('med_sale')
            ->alias('a')
            ->join('medicine b', 'a.medicine_id = b.id')
            ->where(['id' => $id])
            ->select();
        if (!empty($res)) {
            return $this->output_success(20020, $res, '药品销售情况展示成功');
        } else {
            return $this->output_error(20021, '药品销售情况展示失败');
        }
    }
}








