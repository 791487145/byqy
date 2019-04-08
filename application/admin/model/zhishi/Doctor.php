<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/18
 * Time: 11:43 AM
 */
namespace app\admin\model\zhishi;
use app\admin\model\system\SystemConfig;
use basic\ModelBasic;
use traits\ModelTrait;

class Doctor extends ModelBasic{
    use ModelTrait;
    /*
     * 异步获取列表
     * @param $where
     * @return array
     */
    public static function DoctorList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];
        foreach ($data as &$item){
            //$item['doctor_avatar']=SystemConfig::getValue('site_url').$item['doctor_avatar'];
        }
        $count=self::systemPage($where,true)->count();
        return compact('count','data');
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where,$isAjax=false){
        $model = new self;
        if ($where['sid']!= 0)  $model = $model->where('sid',$where['sid']);
        if($where['doctor_name'] != '')  $model = $model->where('doctor_name','LIKE',"%$where[doctor_name]%");
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