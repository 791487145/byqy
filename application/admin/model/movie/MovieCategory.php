<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 10:09 AM
 */
namespace app\admin\model\movie;
use basic\ModelBasic;
use service\UtilService;
use traits\ModelTrait;

class MovieCategory extends ModelBasic{
    use ModelTrait;
    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getTierList($model = null)
    {
        if($model === null) $model = new self();
        return $model->select()->toArray();
    }

    public static function delCategory($id){
        $count = Movie::where('cid',$id)->count();
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
        if($where['title'] != '')  $model = $model->where('title','LIKE',"%$where[title]%");
        if($isAjax===true){
            if(isset($where['sort']) && $where['sort']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
            return $model;
        }
        return self::page($model,function ($item){

        },$where);
    }

}