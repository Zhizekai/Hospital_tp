<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2019/2/12
 * Time: 13:29
 */
namespace app\api\controller;


use redis\Redis;
use think\Controller;
use think\Config;
use think\Db;
use think\Request;
use token\Token;
class Base extends Controller
{
    /**
     * 设置请求头
     */
    public function __construct()
    {
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Methods", "POST, PUT, OPTIONS");
        header('Content-Type:application/json; charset=utf-8');
    }

    /**
     * 空方法处理
     * @return json
     */
    public function _empty()
    {
        return $this->output_error(10001, '请求不存在');
    }

    /**
     * 成功消息输出
     * @param $code
     * @param array $data
     * @param string $msg
     * @return array
     */
    protected function output_success($code, $data = array(), $msg = '')
    {
        $json = [
            'status' => 1,
            'code' => $code,
            'data' => $data,
            'msg' => $msg,
        ];

        return $json;
    }

    /**
     * 失败消息输出
     * @param $code
     * @param string $msg
     * @return array
     */
    protected function output_error($code, $msg = '')
    {
        $json = [
            'status' => 0,
            'code' => $code,
            'msg' => $msg,
        ];
        return $json;
    }

//    前端可封装
    //发送请求
//    function request(ajax, sign) {
//        if (sign != undefined) {
//            if (ajax.data == undefined) ajax.data = {};
//        ajax.data.timestamp = ((new Date()).getTime()) / 1000;
//        ajax.data.token = token;
//        ajax.data.sign = hex_sha1(token + salt + ajax.url.toLowerCase() + ajax.data.timestamp);
//    }
//        $.ajax(ajax);
//    }

    /**
     * 检查sign是否正常
     * @return mixed
     */
    protected function check_sign()
    {
        $token = input('param.token');
        $sign = input('param.sign');
        $timestamp = input('param.timestamp');
        //1. 验证参数是否为空
        //===============
        if (empty($token)) {
            $json = $this->output_error(10005, 'token不能为空');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (empty($sign)) {
            $json = $this->output_error(10006, '签名不能为空');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (empty($timestamp)) {
            $json = $this->output_error(10007, '时间戳不能为空');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

        //2. 验证时间戳是否超时
        //===============
        //2.1 验证请求时间与服务器时间的误差
        $time_now = time();
        $min_time = $time_now - (Config::get('request.over_expire_time')) / 2;
        $max_time = $time_now + (Config::get('request.over_expire_time')) / 2;
        //时间不合法
        if ($timestamp > $max_time || $timestamp < $min_time) {
            $json = $this->output_error(10008, '时间戳错误');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
        //2.2 验证登录状态是否过期
        $redis = Redis::getRedis();
        $token_server_info = $redis->hGetAll('uid_' . $token);
        if ($token_server_info) {
            if (($token_server_info['update_time'] + Config::get('token.expire_time')) < $time_now) {
                $json = $this->output_error(10010, '登录过期');
                echo json_encode($json, JSON_UNESCAPED_UNICODE);
                exit;
            }
        } else {
            $json = $this->output_error(10010, '登录过期');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }


        //3. 验证sign是否合法
        //==============
        //3.1 获取sign_server
        $module = strtolower(Request::instance()->module());
        $controller = strtolower(Request::instance()->controller());
        $action = strtolower(Request::instance()->action());
        $request_uri = '/' . $module . '/' . $controller . '/' . $action;
        $salt = $token_server_info['salt'];
        $sign_server = sha1($token . $salt . $request_uri . $timestamp);

        //3.2 查询是否存在相同的sign
        //签名sign过期时间应该等于token过期时间，保证在token生命周期内不会有重复的sign
        $redis = Redis::getRedis();
        $sign_exist = $redis->get('sign_' . $sign);
        if ($sign_exist) {
            $json = $this->output_error(10009, '签名异常');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }

        //3.3 对比客户端和服务器端签名是否一致
        if ($sign === $sign_server) {
            //存储sign,方便3.2验证
            $redis->setex('sign_' . $sign, Config::get('token.expire_time'), 1);
            return $token;
        } else {

            var_dump($request_uri);
            trace('debug--'.$token .'|'. $salt .'|' . $request_uri  .'|'. $timestamp .'|'.$sign, 'debug');
            $json = $this->output_error(10004, '请求认证失败');
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}