<?php
namespace redis;
use think\Config;
use think\Log;
class Redis
{
    private static $_instance = null; //静态实例

    private function __construct()
    { //私有的构造方法
        self::$_instance = new \Redis();
        $config = Config::get('redis'); // redis配置信息
        $connection=self::$_instance->connect($config['host'], $config['port']);
        if(!$connection){
            Log::write('Redis connection error',ERR);
        }
        if (isset($config['password'])) {
            self::$_instance->auth($config['password']);
        }
    }

    //获取静态实例
    public static function getRedis()
    {
        if (!self::$_instance) {
            new self;
        }
        return self::$_instance;
    }

    /*
     * 禁止clone
     */
    private function __clone(){}
}
