<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/16
 * Time: 17:45
 */

namespace app\api\validate;


use think\Validate;

class UserValidate extends Validate
{

    protected $rule = [
        'mobile' => 'require',
        'code' =>'require',
        'password' => 'require',
    ];

    protected $message = [
        'mobile.require' => '电话不能为空',
        'code.require' => '验证码不能为空',
        'password.require' => '密码不能为空',
    ];
}