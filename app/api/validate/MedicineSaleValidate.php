<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/22
 * Time: 11:20
 */

namespace app\api\validate;


use think\Validate;

class MedicineSaleValidate extends Validate
{

    protected $rule = [
      'num' => 'require',
      'medicine_id' => 'require',
    ];

    protected $message = [
      'num.require' => '请填写数量' ,
      'medicine_id' => '请填写药品id',
    ];
}