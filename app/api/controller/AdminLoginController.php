<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/20
 * Time: 22:35
 */

namespace app\api\controller;



class AdminLoginController extends Base
{

    public function login()
    {
        input('mobile',0,'trim');
        input('name','','trim');


    }

}