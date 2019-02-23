<?php
namespace token;
use think\Config;
use think\Db;
use think\Log;
use redis\Redis;

class Token
{
    /**
     * 创建Token
* 创建规则：
* token=sha1(user_id+secret_key+timestamp_now)
* @param $user_id
*/
    public static function create($user_id){
        //创建salt
        $token['salt']=Token::generate_salt();

        //创建token=sha1(user_id + secret_key+salt+time())
        $token['token']=sha1($user_id.(Config::get('token.secret_key').$token['salt'].time()));

        //存储token
        Token::store($token['token'],$token['salt'],$user_id);
        //返回token
        return $token;
    }

    /**
     * 获取token
     * @param $user_id
     * @return array
     */
    public static function get($user_id){
        $token=Token::create($user_id);
        $token['expire_time']=Config::get('token.expire_time');
       return $token;
    }

    /**
     * 删除token
     * @param $token
     * @return bool|int|string
     * @throws \think\Exception
     */
    public static function delete($token){
        $token_info=db('token')->where('token',$token)->field('id')->find();
        if($token_info){
            $result= db('token')->where('id',$token_info['id'])->update(['status'=>0]);
            $redis=Redis::getRedis();
            $redis->hDel('uid_'.$token,'user_id','update_time','salt','token_id');
            return $result;
        }
        return false;
    }

    /**
     * 刷新token
     * 1. 更新数据库
     * 2. 更新redis hash   uid_$token
     * @param $token_id
     * @return bool
     */
    public static function refresh($token_id,$token){
        if($token_id){
            $update_time=time();
            $token_info=db('token')->where('id',$token_id)->update(['update_time'=>$update_time]);
            if($token_info){
                $redis=Redis::getRedis();
                $redis->hSet('uid_'.$token,'update',$update_time);
                $redis->expire('uid_'.$token,Config::get('token.expire_time'));
            }
            return true;
        }else{
            return false;
        }

    }

    /**
     * 获取user_id
     * @param $token
     * @return int|mixed
     */
    public static function get_user_id($token){
        $redis=Redis::getRedis();
        $user_id=$redis->hGet('uid_'.$token,'user_id');
        if(!$user_id){
            $token_info=db('token')->where('token',$token)->field('user_id')->find();
            if($token_info){
                return $token_info['user_id'];
            }else{
                return 0;
            }
        }else{
            return $user_id;
        }

    }

    /**
     * 存储token
     * 使用redis string
     * key=token
     * value=user_id
     * @param $token
     * @param $user_id
     */
    private static function store($token,$salt,$user_id){
        $timestamp=time();
        $data=[
            'user_id'=>$user_id,
            'token'=>$token,
            'salt'=>$salt,
            'create_time'=>$timestamp,
            'update_time'=>$timestamp,
            'status'=>1,
        ];
        $token_id=db('token')->insertGetId($data);

        if($token_id){
            $redis=Redis::getRedis();
            $redis->hMset('uid_'.$token,['user_id'=>$user_id,'update_time'=>$timestamp,'salt'=>$salt,'token_id'=>$token_id]);
            $redis->expire('uid_'.$token,Config::get('token.expire_time'));
        }
    }

    /**随机生成salt
     * @param int $length
     * @return string
     */
    public static function generate_salt( $length = 8 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars ='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        return $password;
    }
}
