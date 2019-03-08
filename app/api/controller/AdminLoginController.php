<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/20
 * Time: 22:35
 */

namespace app\api\controller;

use think\Db;
use token\Token;
use think\Cache;

class AdminLoginController extends Base
{

    public function login()
    {

        $mobile = input('mobile','','trim');
        $password = input('password','','trim');

        if (empty($mobile) || empty($password)) {
            return $this->output_error(11001, '用户名/密码不能为空');
        }


        $user_id = Db::name('user')->where(['mobile'=>$mobile,'status'=>2])->value('id');

        if (empty($user_id)){
            return $this->output_error(11003,'该管理员尚未注册');
        }

        $user_info = Db::name('user')->where(['id'=>$user_id,'password'=>password($password)])->find();


        if (!empty($user_info)){
            $token_info = Token::get($user_id);
            session('user.id', $user_id);
            return $this->output_success(11101, $token_info, '登录成功');
        }else{
            return $this->output_error(11004,'密码错误！');
        }

    }

}