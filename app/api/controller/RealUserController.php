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

class RealUserController extends Base
{

    /**
     * 后台展示全部个人信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {

            return $this->output_error(404,'无展示个人信息权限');
        };
        //===========

        //0是全部信息，1是通过的信息，2是未审核信息，3是没通过的信息
        $param = input('param',0,'trim');
        $mobile = input('mobile',0,'trim');
        $sfz =input('sfz',0,'trim');
        $where['mobile'] = ['like','%'.$mobile.'%'];
        $where['sfz'] = ['like','%'.$sfz.'%'];


        switch ($param)
        {
            case 0:
                //全部信息
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where($where)
                    ->select();
                break;
            case 1:
                //通过的信息
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>1])//enum设置成字符串形式了，这是一个大坑
                    ->where($where)
                    ->select();
                break;

            case 2:
                //未审核的信息
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>0])//enum设置成字符串形式了，这是一个大坑
                    ->where($where)
                    ->select();
                break;
            case 3:
                $real = new RealUserModel();
                $res = $real->alias('a')
                    ->join('hos_user b','a.user_id = b.id')
                    ->field('name,mobile,sex,age,a.*')
                    ->where(['a.status'=>2])//enum设置成字符串形式了，这是一个大坑
                    ->where($where)
                    ->select();
                //未通过的信息
                break;
        }
        if (!empty($res)) {
            return $this->output_success(200,$res,'name是名字，mobile是电话号，sex性别，age年龄，positive_img身份证前脸，back-img后脸，sfz身份证，');
        }else{
            return $this->output_error(404,'没有这个用户');
        }
    }

    /**
     * 上传个人信息
     * @token
     * @sign
     * @timestamp
     */
    public function add()
    {

        //检验用户有没有登陆并且获取用户id
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

        if (empty($file['pos'])||empty($file['back'])) {
            return $this->output_error(500,'请上传图片');
        }

        if ($file) {

            $info_pos = $file['pos']->move(ROOT_PATH . 'public' . DS . 'upload');
            $info_back = $file['back']->move(ROOT_PATH . 'public' . DS . 'upload');

            if ($info_back&&$info_pos){
                $positive_img = 'http://hospital.weinuoabc.com/upload/'.$info_pos->getSaveName();
                $back_img = 'http://hospital.weinuoabc.com/upload/'.$info_back->getSaveName();
            }else{
                return $this->output_error(404,'图片上传失败');
            }

        }


        $data = [
            'user_id'=>$uid,
            'sfz'   =>$sfz,
            'positive_img' => $positive_img,
            'back_img'=> $back_img,
        ];

        $res = Db::name('real_user')->insert($data);

        if (!empty($res)){
            return $this->output_success(10001,$res,'个人信息上传成功');
        }else{
            return $this->output_error(10010,'个人信息上传失败');
        }
    }

    /**
     * 人工验证
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function check()
    {


        //==========
        //检验管理员是否登陆
        $token = $this->check_sign();

        //检验管理员是否有操作此模块的权限
        if (!$this->check_power($token))
        {
            return $this->output_error(404,'无权限');
        };
        //===========

        //用户的id
        $uid = input('user_id',0,'intval');
        if (empty($uid)){
            return $this->output_error(10010,'请选择用户');
        }
        $param = input('param','','intval');
        if ($param == 1) {
            $res = Db::name('real_user')->where('user_id',$uid)->update(['status'=>1]);
            if (!empty($res)){
                return $this->output_success(10001,[],'这个人实名认证通过');
            }else{
                return $this->output_success(10010,[],'这人实名认证已经通过了，你还想怎么样');
            }
        }

        if ($param == 2) {
            $res = Db::name('real_user')->where('user_id',$uid)->update(['status'=>2]);
            if (!empty($res)){
                return $this->output_success(10001,[],'你让这个人实名认证失败');
            }else{
                return $this->output_success(10010,[],'你已经让这个人实名认证失败了，还想怎么样');
            }
        }

    }



}