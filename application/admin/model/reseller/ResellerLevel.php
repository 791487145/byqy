<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/3/5
 * Time: 3:01 PM
 */
namespace  app\admin\model\reseller;
use basic\ModelBasic;
use traits\ModelTrait;

class  ResellerLevel extends ModelBasic{
    use ModelTrait;

    /**
     * 获取代理级别总数
     * @return int|string
     */
    public static function getResellerCount(){
        return self::count();
   }

    /**
     * 重新更新代理层级
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function sortLevel(){
        $res=true;
        $list=self::order('resell_level')->select();
        foreach ($list as $value){
            $res=$value->setInc('resell_level',1);
        }
        return $res;
    }
}