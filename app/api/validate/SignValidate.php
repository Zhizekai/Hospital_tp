<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\api\validate;

use think\Validate;

class SignValidate extends Validate
{
    protected $rule = [
        'top_pressure' => 'require',
        'buttom_pressure'  => 'require',
        'heart_rate' => 'require',
    ];

    protected $message = [
        'top_pressure.require' => '舒张压不能为空',
        'buttom_pressure.require'  => '收缩压不能为空',
        'heart_rate.require'  => '心率不能为空',
    ];

}