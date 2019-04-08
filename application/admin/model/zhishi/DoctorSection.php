<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/18
 * Time: 10:43 AM
 */
namespace app\admin\model\zhishi;
use basic\ModelBasic;
use service\UtilService;
use traits\ModelTrait;

class  DoctorSection extends ModelBasic{
    use ModelTrait;
    /*
     * 异步获取列表
     * @param $where
     * @return array
     */
    public static function SectionList($where){
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
        if($where['section_name'] != '')  $model = $model->where('section_name','LIKE',"%$where[section_name]%");
        if($isAjax===true){
            if(isset($where['order']) && $where['order']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
            return $model;
        }
        return self::page($model,function ($item){
        },$where);
    }
    public static function delCategory($id){
        return self::del($id);
    }
}