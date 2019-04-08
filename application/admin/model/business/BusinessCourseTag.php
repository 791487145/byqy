<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 12:06 PM
 */
namespace app\admin\model\business;
use basic\ModelBasic;
use service\UtilService;
use traits\ModelTrait;

class BusinessCourseTag extends ModelBasic{
    use ModelTrait;

    public static function delTag($id){
        return self::del($id);

    }
    /*
    * 异步获取分类列表
    * @param $where
    * @return array
    */
    public static function TagList($where){
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
        if($where['tag_name'] != '')  $model = $model->where('tag_name','LIKE',"%$where[tag_name]%");
        if($isAjax===true){
            if(isset($where['order']) && $where['order']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
            return $model;
        }
        return self::page($model,null,$where);
    }


}