<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 3:25 PM
 */
namespace  app\admin\model\movie;
use basic\ModelBasic;
use traits\ModelTrait;

class Movie extends ModelBasic{
    use ModelTrait;

    /*
     * 课程标签
     */
    public function tags(){
        return $this->belongsToMany('CourseTag','course_tag_relation','tag_id','course_id');
    }
    /*
     * 课程分类
     */
    public function category(){
        return $this->hasOne('CourseCategory','id','cid');
    }
    /*
     * 异步获取列表
     * @param $where
     * @return array
     */
    public static function CourseList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];
        foreach ($data as &$item){
            $item['category']=MovieCategory::get($item['cid'])?MovieCategory::get($item['cid'])->value('title'):'';
            $item['good_name'] = StoreProduct::where('id',$item['goods_id'])->value('store_name');
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
        if($where['is_show'] != '')  $model = $model->where('is_show',$where['is_show']);
        else if ($where['cid']!='')  $model = $model->where('cid',$where['cid']);
        if($where['title'] != '')  $model = $model->where('title','LIKE',"%$where[title]%");
        if($where['type'] != '')  $model = $model->where('type',$where['type']);
        if($isAjax===true){
            if(isset($where['sort']) && $where['sort']!=''){
                $model=$model->order(self::setOrder($where['sort']));
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