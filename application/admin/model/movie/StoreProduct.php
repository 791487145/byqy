<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\movie;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemConfig;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class StoreProduct extends ModelBasic
{
    use ModelTrait;

    /**删除产品
     * @param $id
     */
    public static function proDelete($id){
//        //删除产品
//        //删除属性
//        //删除秒杀
//        //删除拼团
//        //删除砍价
//        //删除拼团
//        $model=new self();
//        self::beginTrans();
//        $res0 = $model::del($id);
//        $res1 = StoreSeckillModel::where(['product_id'=>$id])->delete();
//        $res2 = StoreCombinationModel::where(['product_id'=>$id])->delete();
//        $res3 = StoreBargainModel::where(['product_id'=>$id])->delete();
//        //。。。。
//        $res = $res0 && $res1 && $res2 && $res3;
//        self::checkTrans($res);
//        return $res;
    }

}