<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/13
 * Time: 2:37 PM
 */
namespace app\admin\model\business;
use basic\ModelBasic;
use traits\ModelTrait;

class BusinessCourseCatalog extends ModelBasic{
    use ModelTrait;

    /*
    * 异步获取列表
    * @param $where
    * @return array
    */
    protected $type=['0'=>'图文','1'=>'音频','2'=>'视频'];
    public static function CatalogList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];
        foreach ($data as &$item){
            $item['course_name']=BusinessCourse::where('id',$item['cid'])->value('course_name');
            $item['add_time']=date('Y-m-d h:i:s',$item['add_time']);
            $item['type']=(new self)->getType($item['type']);
        }
        $count=self::systemPage($where,true)->count();
        return compact('count','data');
    }
    public function getType($index){
        return $this->type[$index];
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where,$isAjax=false){
        $model = new self;
        $model = $model->where('cid',$where['cid']);
        if($where['catalog_title'] != '')  $model = $model->where('catalog_title','LIKE',"%$where[catalog_title]%");
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