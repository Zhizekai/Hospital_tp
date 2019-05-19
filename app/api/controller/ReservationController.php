<?php
namespace app\api\controller;



use think\Db;

class ReservationController extends Base
{
    public function zzk()
    {
        echo 'dfsdfdsfsdfsfs';
    }
    public function reservation()
    {
        $name = input('name','','trim');
        $phone = input('phone','','trim');
        $comment = input('comment','','trim');

        if (empty($name)) {
            return $this->dang_output_error('400',[],'请输入姓名');
        }

        if (empty($phone)) {
            return $this->dang_output_error('400',[],'请输入电话');
        }
//        if (empty($comment)) {
//            return $this->output_error('400',[],'请输入备注');
//        }
        $res = Db::name('reservation')->insert([
            'name'=>$name,
            'phone'=>$phone,
            'comment'=>$comment,
            'create_time'=>time(),
            'update_time'=>time()
        ]);

        if ($res) {
            return $this->output_success('200',[],'预约成功');
        }else {
            return $this->dang_output_error('400',[],'预约失败');
        }
    }
}
