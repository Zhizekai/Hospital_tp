<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2019/2/14
 * Time: 16:47
 */

namespace app\api\controller;


use think\Db;
use token\Token;
class LoginController extends Base
{
    /**
     * 用户登陆接口
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(){
        $mobile = input('mobile','','trim');
        $password = input('password','','trim');

        if (empty($mobile) || empty($password)) {
            return $this->output_error(11001, '用户名/密码不能为空');
        }
        if (!isMobile($mobile)){
            return $this->output_error(11002,'请输入正确的手机号');
        }

        $user_id = Db::name('user')->where(['mobile'=>$mobile,'status'=>1])->value('id');

        if (empty($user_id)){
            return $this->output_error(11003,'该手机号尚未注册');
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