<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/20
 * Time: 19:56
 */

namespace app\api\controller;


use think\Db;
use think\cache;

class AdminController extends Base
{

    /**
     * 管理员列表
     */
    public function index()
    {

        $uid = input('user_id',0,'intval');
        $model = input('model',0,'intval');
        if ($model == 0){
            return $this->output_error(500,'请传入模块');
        }
        //判断管理员有没有权限
        if (!$this->check_power($uid,$model)){
            return $this->output_error(500,'无权限');
        }


        $result = Db::name('user')
            ->field('id,mobile,name,power_ids')
            ->where(['status'=>2])
            ->select()->toArray();

        foreach ($result as $key => $value) {
            $res = $this->get_power($value['power_ids']);
            $result[$key]['power_ids'] = $res;
        }


        if ($result) {
            return $this->output_success(10011, $result, '这都是管理员');
        } else {
            return $this->output_error(10003, '请求失败');
        }
    }

    /**
     * 寻找权限
     * @param string
     */
    private function get_power($power_ids)
    {
        //按照逗号切割字符串
        $ids = explode(',', $power_ids);
        foreach ($ids as $key => $value) {
            $power = Db::name('power')->where([
                'id' => $value,
                'status' => 0,
            ])->field('name')->find();

            $ids[$key] = $power;

        }
        return $ids;
    }

    /**
     * 添加管理员
     */
    public function add()
    {

        //当前用户的id
        $uid = input('user_id',0,'intval');

        //模块的id
        $model = input('model',0,'intval');
        if ($model == 0){
            return $this->output_error(500,'请传入模块');
        }
        //判断管理员有没有权限
        if (!$this->check_power($uid,$model)){
            return $this->output_error(500,'无权限');
        }

        $mobile = input('mobile', 0, 'trim');
        $name = input('name', 0, 'trim');
        $password = input('password', 0, 'trim');
        $power_ids = input('power_ids', 0, 'trim');

        if (empty($mobile)) {
            return $this->output_error(1000, '请输入电话号码');
        }
        if (isMobile($mobile)) {
            return $this->output_error(200, '请输入正确的电话号码');
        }
        if (empty($name)) {
            return $this->output_error(10010, '请输入姓名');
        }
        if (empty($password)) {
            return $this->output_error(10010, '请输入密码');
        }
        if (empty($power_ids)) {
            return $this->output_error(10001, '请分配权限');
        }

        $data = [
            'mobile' => $mobile,
            'name' => $name,
            'password' => password($password),
            'power_ids' => $power_ids,
            'status' => 2,
        ];
        $res = Db::name('power')->insert($data);


        if (!empty($res)) {
            return $this->output_success(10010, $res, '账号分配成功');
        } else {
            return $this->output_error(11000, '账号分配失败');
        }
    }

    /**
     * 修改管理员
     *
     */
    public function update()
    {


        //当前用户的id
        $uid = input('user_id',0,'intval');

        //模块的id
        $model = input('model',0,'intval');
        if ($model == 0){
            return $this->output_error(500,'请传入模块');
        }
        //判断管理员有没有权限
        if (!$this->check_power($uid,$model)){
            return $this->output_error(500,'无权限');
        }


        //要修改的管理员的id
        $id = input('id', 0, 'intval');
        $mobile = input('mobile', 0, 'trim');
        $name = input('name', 0, 'trim');
        $password = input('password', 0, 'trim');
        $power_ids = input('power_ids', 0, 'trim');

        if (empty($id)) {
            return $this->output_error(10001, '请输入id号');
        }
        if (empty($mobile)) {
            return $this->output_error(1000, '请输入电话号码');
        }
        if (isMobile($mobile)) {
            return $this->output_error(200, '请输入正确的电话号码');
        }
        if (empty($name)) {
            return $this->output_error(10010, '请输入姓名');
        }
        if (empty($password)) {
            return $this->output_error(10010, '请输入密码');
        }
        if (empty($power_ids)) {
            return $this->output_error(10001, '请分配权限');
        }

        $data = [
            'mobile' => $mobile,
            'name' => $name,
            'password' => password($password),
            'power_ids' => $power_ids,
        ];
        $res = Db::name('power')->where('id', $id)->update($data);


        if (!empty($res)) {
            return $this->output_success(10010, $res, '账号修改成功');
        } else {
            return $this->output_error(11000, '账号修改失败');
        }
    }

    /**
     * 删除管理员
     */
    public function delete()
    {

        //当前用户的id
        $uid = input('user_id',0,'intval');

        //模块的id
        $model = input('model',0,'intval');
        if ($model == 0){
            return $this->output_error(500,'请传入模块');
        }
        //判断管理员有没有权限
        if (!$this->check_power($uid,$model)){
            return $this->output_error(500,'无权限');
        }

        $id = input('id', 0, 'intval');
        if (empty($id)) {
            return $this->output_error(10001, '请输入id号');
        }
        $res = Db::name('user')->where('id',$id)->update('status',0);

        if (!empty($res)) {
            return $this->output_success(10010,[], '管理员删除成功');
        } else {
            return $this->output_error(11000, '管理员删除失败');
        }
    }

}