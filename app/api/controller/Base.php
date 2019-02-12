<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2019/2/12
 * Time: 13:29
 */
namespace app\api\controller;

use think\Controller;

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
            'status' => 1,
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
            'status' => 0,
            'msg' => $msg,
        ];
        return $json;
    }

}