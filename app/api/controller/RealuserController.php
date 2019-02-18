<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/17
 * Time: 17:12
 */

namespace app\api\controller;


use app\api\model\RealUserModel;
use think\Db;
use think\File;
use cmf\lib\Upload;

class RealuserController extends Base
{

    /**
     * 后台展示全部个人信息
     * input param
     */
    public function index()
    {

        //0是全部信息，1是通过的信息，2是未审核信息，3是没通过的信息
        $param = input('param','','invtal');

        if (empty($param)) {
            return $this->output_error(10010,'请给出参数');
        }
        switch ($param)
        {
            case 0:
                //全部信息
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->select();
                break;
            case 1:
                //通过的信息
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>'1'])//enum设置成字符串形式了，这是一个大坑
                    ->select();
                break;

            case 2:
                //未审核的信息
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>'0'])//enum设置成字符串形式了，这是一个大坑
                    ->select();
                break;
            case 3:
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>'2'])//enum设置成字符串形式了，这是一个大坑
                    ->select();
                //未通过的信息
                break;
        }
        if (!empty($res)) {
            return $this->output_success(200,$res,'name是名字，mobile是电话号，sex性别，age年龄，
            positive_img身份证前脸，back-img后脸，sfz身份证，');
        }else{
            return $this->output_error(404,'没有这个用户');
        }
    }

    /**
     * 上传个人信息
     * input token
     * input sign
     * input timestamp
     */
    public function add()
    {
        //获取用户id
        $uid = $this->getuid();
        if (empty($uid)) {
            return $this->output_error(10002, '请先登陆');
        }

        $sfz = input('sfz', [], 'trim');
        if (!isShenfenzheng($sfz)){
            return $this->output_error(10001,'请输入正确的身份证');
        }

        $file['pos'] = request()->file('positive_img');
        $file['back'] = request()->file('back_img');

        if ($file) {

            $info_pos = $file['pos']->move(ROOT_PATH . 'public' . DS . 'upload');
            $info_back = $file['back']->move(ROOT_PATH . 'public' . DS . 'upload');

            if ($info_back&&$info_pos){
                $positive_img = 'http://hospital.weinuoabc.com/upload/'.$info_pos->getSaveName();
                $back_img = 'http://hospital.weinuoabc.com/upload/'.$info_back->getSaveName();
            }else{
                return $this->output_error(404,'图片上传失败');
            }

        }else{
            return $this->output_error(500,'请上传图片');
        }

        $data = [
            'user_id'=>$uid,
            'sfz'   =>$sfz,
            'positive_img' => $positive_img,
            'back_img'=> $back_img,
            'create_time' => time(),
        ];

        $res = Db::name('real_user')->insert($data);

        if (!empty($res)){
            return $this->output_success(10001,$res,'个人信息上传成功');
        }else{
            return $this->output_error(10010,'个人信息上传失败');
        }
    }

    /**
     * 人工验证个人信息
     * 通过
     */
    public function check()
    {


        $uid = input('user_id',0,'invtal');
        if (empty($uid)){
            return $this->output_error(10010,'请选择用户');
        }
        $param = input('param','','intval');
        switch ($param)
        {
            case 1:
                //审核通过
                $res = Db::name('real_user')->where('user_id',$uid)->update('status',1);
                break;
            case 2:
                //审核未通过
                $res = Db::name('real_user')->where('user_id',$uid)->update('status',2);
                break;
        }
        if (!empty($res)){
            return $this->output_success(10001,[],'这个人实名认证通过');
        }else{
            return $this->output_error(10010,'操作失败');
        }
    }



}