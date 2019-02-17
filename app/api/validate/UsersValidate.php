<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/16
 * Time: 17:45
 */

namespace app\api\validate;


class UsersValidate
{

    protected $rule = [
        'mobile' => 'require',
        'name' => 'require',
        'sex' => 'require',
        'age' => 'require',
        'weight' => 'require',
        'height' => 'require',
        'emergency_mobile' => 'require',
        'password' => 'require',
    ];

    protected $message = [
        'mobile.require' => '电话不能为空',
        'name.require' => '名字不能为空',
        'sex.require' => '性别不能为空',
        'age.require' => '年龄不能为空',
        'weight.require' => '体重不能为空',
        'height.require' => '身高不能为空',
        'emergency_mobile.require' => '紧急电话不能为空',
        'password.require' => '密码不能为空',
    ];
}