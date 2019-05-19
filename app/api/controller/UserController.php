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
use token\Token;

vendor('jiguang.jsms.src.JSMS');


class UserController extends Base
{

    /**
     * 测试方法
     */
    public function zzk()
    {
        $res = Db::name('med_record')->alias('a')
            ->join('medicine b','a.medicine_id = b.id')
            ->field('a.*,b.name,b.attention,b.price')
            ->where('a.is_deleted',0)
            ->update(['a.is_deleted'=>1]);

        if ($res){
            return $this->output_success(10011,$res,'用药删除成功');
        }else{
            return $this->output_success(10003,$res,'用药提醒删除失败');
        }
    }

    /**
     * 用户注册接口
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register()
    {
        //获取    电话  密码  验证码
        $mobile = input('mobile', 0,'trim');
        $password = input('password','','trim');
        $code = input('code','','trim');


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
     * 忘记密码接口
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function forget()
    {
        //获取    电话  密码  验证码
        $mobile = input('mobile', 0,'trim');
        $password = input('password','','trim');
        $code = input('code','','trim');


        $msg_id = cache::get($mobile);
        $check_code = $this->check_code($msg_id, $code);
        if (!$check_code) {
            $this->output_error(10010,'验证码不对');
        }


        $check = Db::name('user')->where('mobile', $mobile)->find();

        if (empty($check)) {
            return $this->output_success(10011,[],'该号码未注册');
        } else {
            $result = Db::name('user')->where('mobile',$mobile)->update(['password'=>password($password)]);
        }

        if ($result){
            return $this->output_success(10011,[],'修改密码成功');
        }else{
            return $this->output_error(10003,'修改密码失败');
        }
    }

    /**
     * @return array
     */
    public function mobile_login()
    {

        $mobile = input('mobile',0,'trim');
        $code = input('code',0,'trim');

        if (empty($mobile)) {
            return $this->output_error(11001, '手机号不能为空');
        }


        $user_id = Db::name('user')->where(['mobile'=>$mobile,'status'=>1])->value('id');

        if (empty($user_id)){
            return $this->output_error(11003,'该手机号尚未注册');
        }

        $msg_id = cache::get($mobile);
        $check_code = $this->check_code($msg_id, $code);
//        if (!$check_code) {
//            $this->output_error(10010,'验证码不对');
//        }

        $token_info = Token::get($user_id);
        session('user.id', $user_id);
        return $this->output_success(11101, $token_info, '登录成功');
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
        cache::set($mobile, $response['body']['msg_id'], 120);

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

        if (!array_key_exists('is_valid',$response['body'])) {
            $json = json_encode($this->output_error(500,'此手机号今天获取验证码次数过多，等到明天再发吧，人家有点吃不消了呢'),JSON_UNESCAPED_UNICODE);
            echo $json;exit;
        }
        return $response['body']['is_valid'];
    }

    /**
     * 注销
     * @return array|string
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