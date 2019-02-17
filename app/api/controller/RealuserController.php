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
     * 先后台展示用户的所有个人信息
     */
    public function index()
    {

        $real = new RealUserModel();
        $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>'0'])//enum设置成字符串形式了，这是一个大坑
                    ->select();

        if (!empty($res)) {
            return $this->output_success(200,$res,'name是名字，mobile是电话号，sex性别，age年龄，
            positive_img身份证前脸，back-img后脸，sfz身份证，');
        }else{
            return $this->output_error(404,'没有这个用户');
        }
    }

    /**
     * 上传个人信息
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

        $data = ['user_id'=>$uid];

        $res = Db::name('real_user')->data($data);


    }

}