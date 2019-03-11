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
use token\Token;

class AdminController extends Base
{

    /**
     * 管理员列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zzk()
    {



    }
    public function index()
    {

        //检验管理员是否登陆
        $token = $this->check_sign();

        $uid = Token::get_user_id($token);
        $user_role = Db::name('user')->where('id',$uid)->value('user_role');

        //检验管理员是否有操作此模块的权限
        if (!$user_role == 1)
        {
            return $this->output_error(404,'无权限');
        };


        $name = input('name','','trim');
        $mobile = input('mobile','','trim');



        $where['name'] = ['like','%'.$name.'%'];
        $where['mobile'] = ['like','%'.$mobile.'%'];
        $where['status'] = 2;

        $result = Db::name('user')->field('id,mobile,name,power_ids,password')->where($where)->select()->toArray();

        foreach ($result as $key => $value) {


            $ids = explode(',', $value['power_ids']);

            foreach ($ids as $key1 => $value1) {
                $power = Db::name('power')->where(['id' => $value1, 'status' => 0,])->value('name');

                $ids_name[$key1] = $power;

            }

            $result[$key]['power_ids'] = $ids;
            $result[$key]['power_ids_name'] = $ids_name;
        }


        if ($result) {
            return $this->output_success(10011, $result, '这都是管理员');
        } else {
            return $this->output_error(10003, '请求失败');
        }
    }


    /**
     * 添加管理员
     */
    public function add()
    {

        //检验管理员是否登陆
        $token = $this->check_sign();

        $uid = Token::get_user_id($token);
        $user_role = Db::name('user')->where('id',$uid)->value('user_role');

        //检验管理员是否有操作此模块的权限
        if (!$user_role == 1)
        {
            return $this->output_error(404,'无权限');
        };

        $mobile = input('mobile', 0, 'trim');
        $name = input('name', 0, 'trim');
        $password = input('password', 0, 'trim');
        $power_ids = input('power_ids/a');



        if (empty($mobile)) {
            return $this->output_error(1000, '请输入电话号码');
        }

        if (empty($name)) {
            return $this->output_error(10010, '请输入姓名');
        }
        if (empty($password)) {
            return $this->output_error(10010, '请输入密码');
        }
        if (empty($power_ids)) {
            return $this->output_success(10001,$power_ids,'请分配权限');
        }

        $ac = Db::name('user')->where('mobile',$mobile)->find();
        if  ($ac) {
            return $this->output_error(400,'账号已经存在');
        }

        $data = [
            'mobile' => $mobile,
            'name' => $name,
            'password' => $password,
            'power_ids' => implode(',',$power_ids),
            'status' => 2,
        ];
        $res = Db::name('user')->insert($data);


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


        //检验管理员是否登陆
        $token = $this->check_sign();

        $uid = Token::get_user_id($token);
        $user_role = Db::name('user')->where('id',$uid)->value('user_role');

        //检验管理员是否有操作此模块的权限
        if (!$user_role == 1)
        {
            return $this->output_error(404,'无权限');
        };


        //要修改的管理员的id
        $id = input('id', 0, 'intval');
        $mobile = input('mobile', 0, 'trim');
        $name = input('name', 0, 'trim');
        $password = input('password', 0, 'trim');
        $power_ids = input('power_ids/a');

        if (empty($id)) {
            return $this->output_error(10001, '请输入id号');
        }
        if (empty($mobile)) {
            return $this->output_error(1000, '请输入电话号码');
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
            'password' => $password,
            'power_ids' => implode(',',$power_ids),
        ];
        $res = Db::name('user')->where('id', $id)->update($data);


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

        //检验管理员是否登陆
        $token = $this->check_sign();

        $uid = Token::get_user_id($token);
        $user_role = Db::name('user')->where('id',$uid)->value('user_role');

        //检验管理员是否有操作此模块的权限
        if (!$user_role == 1)
        {
            return $this->output_error(404,'无权限');
        };

        $id = input('id', 0, 'intval');
        if (empty($id)) {
            return $this->output_error(10001, '请输入id号');
        }
        $res = Db::name('user')->where('id',$id)->update(['status'=>0]);

        if (!empty($res)) {
            return $this->output_success(10010,[], '管理员删除成功');
        } else {
            return $this->output_error(11000, '管理员删除失败');
        }
    }

}