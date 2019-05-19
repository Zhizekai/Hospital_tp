<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/3/21
 * Time: 21:17
 */

namespace app\api\controller;

use think\Db;

class AdminReservationController extends Base
{

    public function index()
    {
        $status = input('status','','trim');
        $where = [];
        $where['status'] = ['like','%'.$status.'%'];
        $res = Db::name('reservation')->where($where)->order('status','asc')->select();
        if ($res) {
            return $this->output_success('200',$res,'预约列表获取成功');
        }else {
            return $this->dang_output_error('400',[],'预约列表获取失败');
        }

    }
    public function check()
    {

        $status = input('status',0,'trim');
        $id = input('id',0,'int');


        if (empty($id)) {
            return $this->dang_output_error('400',[],'请指出是哪条预约');
        }

        $res = Db::name('reservation')->where(['id'=>$id])->update(['status'=>$status,'update_time'=>time()]);

        if ($res) {
            return $this->output_success('200',$res,'洗完车了');
        }else {
            return $this->dang_output_error('400',[],'操作失败');
        }
    }

}