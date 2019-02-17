<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/1/29
 * Time: 15:48
 */

namespace app\index\controller;

use app\index\model\users;
use Think\Db;


class RecordReminds extends Base
{
    /**
     * 前台展示你该吃什么药了
     */
    public function show()
    {
        $token = input('token','');

        if (empty($token)) {
            return '请登录';
        }

        $result = Db::table('record_reminds')
                    ->where('user_id',users::get_user_id($token))
                    ->select();

        Base::outcome($result);
    }

}