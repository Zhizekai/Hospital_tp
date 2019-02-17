<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 2019/2/17
 * Time: 16:07
 */

namespace app\api\controller;


use think\Db;

class VersionController extends Base
{
    /**
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 根据更新时间降序排序，只展示版本号
     */
    public function index()
    {
        $res = Db::name('version')->order('update_time desc')->field('version')->find();
        if (!empty($res)){
            return $this->output_success(10010,$res,'这是最新的版本号');
        }else{
            return $this->output_error(10001,'体征列表获取失败!');
        }
    }

    /**
     * 修改新版本
     */
    public function update()
    {
        $version = input('version','','trim');

        if (empty($version)) {
            return $this->output_error(10001,'你没输入版本号');
        }
        //以年月日小时分钟秒的字符串的形式输出时间
       //$update_time = date('Y-m-d h:i:s', time());

        $res = Db::name('version')->where('id',1)->update(['version'=>$version,'update_time'=>time()]);

        if (!empty($res)) {
            return $this->output_success(10011,[],'版本号修改成功');
        }else{
            return $this->output_error(10000,'版本号修改失败');
        }
    }
}