<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/1/29
 * Time: 18:01
 */

namespace app\api\controller;


use app\api\model\UserModel;
use think\Db;
use think\Cache;
use JiGuang\JSMS;

vendor('jiguang.jsms.src.JSMS');


class UserController extends Base
{

    /**
     * 测试方法
     */
    public function zzk()
    {
        var_dump(date('Y-m-d',time()));
    }


    /**
     * 用户注册
     * @return array
     */
    public function register()
    {
        //获取    电话  密码  验证码
        $mobile = input('mobile', 0,'trim');
        $password = input('password','','trim');
        $code = input('code','','trim');

        $data = [
          'mobile' =>$mobile,
          'password' => $password,
          'code' => $code,
        ];

        //验证器信息都得让他填了
        $dd = $this->validate($data,'User');
        if (!empty($dd)) {
            return $this->output_error(10010,$dd);
        };


        $msg_id = cache::get($mobile);
        $check_code = $this->check_code($msg_id, $code);
        if (!$check_code) {
            $this->output_error(10010,'验证码不对');
        }

        $check = Db::name('user')->where('mobile', $mobile)->find();

        if (!empty($check)) {
            return $this->output_success(10011,[],'该号码已注册');
        } else {
            $data = [
                'mobile' => $mobile,
                'password' => password($password),
            ];

            $user = new UserModel();
            $result = $user->validate(true)->insert($data);
        }

        if ($result){
            return $this->output_success(10011,[],'用户注册成功');
        }else{
            return $this->output_error(10003,'用户注册失败');
        }

    }

    /**
     * 发送验证码
     */
    public function send()
    {
        $appKey = 'ca0aeedf22605dc9c7433a48';
        $masterSecret = '289cc110f8f2c0a97a85dbeb';

        //获取手机号
        $mobile = input('mobile', '','trim');


        if (empty($mobile)) {
            return $this->output_error(10001,'你没输手机号');
        }

        $client = new JSMS($appKey, $masterSecret, ['ssl_verify' => false]);

        // 发送文本验证码短信
        $response = $client->sendVoiceCode($mobile);

        //设置缓存
        cache::set($mobile, $response['body']['msg_id'], 60);

        return $this->output_success(10010,[],'已经向手机号为' . $mobile . '的用户发送语音验证码');
    }

    /**
     * 验证验证码
     * @return string
     */
    public function check_code($msg_id, $code)
    {
        $appKey = 'ca0aeedf22605dc9c7433a48';
        $masterSecret = '289cc110f8f2c0a97a85dbeb';

        $client = new JSMS($appKey, $masterSecret, ['ssl_verify' => false]);

        $response = $client->checkCode($msg_id, $code);
        return $response['body']['is_valid'];
    }
    /**
     * 注销
     */
    public function login_out()
    {
        $token = input('token','');
        if (empty($token)){
            return '你还没登陆了，注什么销';
        }
        $result = Db::name('token')->where('token',$token)->setField('token',0);
        if ($result){
            return $this->output_success(10011,[],'注销成功');
        }else{
            return $this->output_error(10003,'注销失败');
        }
    }


}