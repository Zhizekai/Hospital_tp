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
use think\Session;
use think\Cache;
use JiGuang\JSMS;
use think\File;
use think\View;

vendor('jiguang');

//require '../../../simplewind/vendor/autoload.php';

class UsersController extends Base
{
    /**
     * 用户注册
     * @return string
     */
    public function register()
    {
        $mobile = input('mobile', 0,'intval');
        $name = input('name','','trim');
        $sex = input('sex',0,'intval');
        $age = input('age',0,'intval');
        $weight = input('weight',0,'intval');
        $height = input('height',0,'intval');
        $emergency_mobile = input('emergency_mobile',0,'intval');
        $password = input('password','','trim');

//        if (empty($phone)) {
//            return '你没输电话号码';
//        }
//        if (empty($password)) {
//            return '你没输密码';
//        }
//        if (empty($name)) {
//            return '你没输名字';
//        }
//        if (empty($sex)) {
//            return '你没输性别';
//        }
//        if (empty($age)) {
//            return '你没输年龄';
//        }
//        if (empty($weight)) {
//            return '你没输体重';
//        }
//        if (empty($height)) {
//            return '你没输身高';
//        }
//        if (empty($code)) {
//            return '你没输验证码';
//        }
//        if (empty($emergency_mobile)) {
//            return '你没输紧急电话';
//        }

//        $msg_id = cache::get($phone);
//        $check_code = $this->check_code($msg_id, $code);
//        if (!$check_code) {
//            return '验证码不对，这是你手机么';
//        }

        $check = Db::name('user')->where('mobile', $mobile)->find();


        if (!empty($check)) {
            return '该号码已经注册';
        } else {
            $data = [
                'mobile' => $mobile,
                'name'  => $name,
                'sex'   => $sex,
                'age'   => $age,
                'weight'=> $weight,
                'height'=> $height,
                'emergency_mobile' => $emergency_mobile,
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
     * @return string
     */
    public function send()
    {
        $appKey = 'ca0aeedf22605dc9c7433a48';
        $masterSecret = '289cc110f8f2c0a97a85dbeb';

        //获取手机号
        $phone = input('phone', '');

        if (empty($phone)) {
            return '你没输手机号';
        }

        $client = new JSMS($appKey, $masterSecret, ['ssl_verify' => false]);

        // 发送文本验证码短信
        $response = $client->sendVoiceCode($phone);

        //设置缓存
        cache::set($phone, $response['body']['msg_id'], 60);

        return '已经向手机号为' . $phone . '的用户发送语音验证码';
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
     * 生成token
     * @return string
     */
    private function makeToken()
    {
        $str = md5(uniqid(md5(microtime(true)), true));
        $str = sha1($str);
        return $str;
    }
    /**
     * 用户登陆
     * @return string
     */
    public function Login()
    {
        $phone = input('phone', '');
        $password = input('password', '');

        if (empty($phone)) {
            return '你没输电话';
        }
        if (empty($password)) {
            return '你没输密码';
        }


        $user_isset = Db::table('users')->where('phone', $phone)->find();
        if ($user_isset == null) {
            return json_encode(['msg' => "用户不存在"]);
        } else {
            $userpsisset = Db::table('users')->where('phone', $phone)->where('password', sha1(md5($password)))->find();
            if ($userpsisset == null) {
                return json_encode(['msg' => "密码错误"]);
            } else {
                $token = $this->makeToken();

                /**设置超时时间*/
                $time_out = strtotime("+7 days");

                $res = Db::table('users')->where('phone', $phone)->update(['time_out' => $time_out, 'token' => $token]);
                if ($res) {
                    return json_encode(["msg" => "登录成功", "token" => $token]);
                }
            }
        }
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


    public function index()
    {
        $view = new View();
        $data = [
            'sbb' => '我是底层架构师',
            'zhi' => '高并发架构师',
        ];

        $view->assign('bbb',$data);

        return $view->fetch('index');
    }

}