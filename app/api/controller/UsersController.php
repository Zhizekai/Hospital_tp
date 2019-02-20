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
use think\response\Json;
use think\Session;
use think\Cache;
use JiGuang\JSMS;
use think\File;
use think\View;

vendor('jiguang.jsms.src.JSMS');

//require '../../../simplewind/vendor/autoload.php';

class UsersController extends Base
{
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

        if (empty($mobile)||empty($password)) {
            $this->output_error(10001,'手机号/密码不能为空');
        }

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
     * @return string
     */
    public function login_out()
    {
        $token = input('token','');
        if (empty($token)){
            return '你还没登陆了，注什么销';
        }
        $result = Db::table('users')->where('token',$token)->setField('token',null);
        self::outcome($result);
    }

}