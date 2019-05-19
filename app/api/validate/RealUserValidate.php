<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/21
 * Time: 13:24
 */

namespace app\api\validate;


use think\Validate;

class RealUserValidate extends Validate
{

    protected $rule = [
       'param' => 'require',
    ];

    protected $message = [
      'param.require' => '无参数无法做判断'
    ];
}