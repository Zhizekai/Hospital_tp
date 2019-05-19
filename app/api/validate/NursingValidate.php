<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/22
 * Time: 10:32
 */

namespace app\api\validate;


use think\Validate;

class NursingValidate extends Validate
{

    protected $rule = [
        'id' => 'require',
        'item' => 'require',
        'record_time' => 'require',
        'frequency'  => 'require',
        'times'  => 'require',
        'man'   => 'require',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'item.require' => '护理项目不能为空',
        'record_time.require' => '提醒时间不能为空',
        'frequency,require' => '频率不能为空',
        'times'   => '次数不能为空',
        'man'   => '负责人不能为空',
    ];

}