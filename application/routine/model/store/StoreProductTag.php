<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\routine\model\store;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemConfig;

/**
 * 产品标签管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class StoreProductTag extends ModelBasic
{
    use ModelTrait;

    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getTierList($model = null)
    {
        if($model === null) $model = new self();
        $model = $model->where('is_show',1)->order('sort DESC');
        return $model->select()->toArray();
    }

    public static function delCategory($id){
        $count = StoreProduct::where('tag_id',$id)->where('is_del',0)->count();
        if($count)
            return false;
        else{
            return self::del($id);
        }
    }
    /*
     * 异步获取分类列表
     * @param $where
     * @return array
     */
    public static function CategoryList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];

        $count=self::systemPage($where,true)->count();
        return compact('count','data');
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where,$isAjax=false){
        $model = new self;

        if($where['is_show'] != '')  $model = $model->where('is_show',$where['is_show']);
        if($where['tag_name'] != '')  $model = $model->where('title','LIKE',"%$where[tag_name]%");
        if($isAjax===true){
            return $model;
        }
        return self::page($model,function ($item){

        },$where);
    }
}