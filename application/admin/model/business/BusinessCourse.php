<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 3:25 PM
 */
namespace  app\admin\model\business;
use basic\ModelBasic;
use traits\ModelTrait;
use app\admin\model\business\BusinessCourseRelation;
use app\admin\model\business\BusinessCourseCatalog;
class BusinessCourse extends ModelBasic{
    use ModelTrait;

    /*
     * 课程标签
     */
    public function tags(){
        return $this->belongsToMany('BusinessCourseTag','business_course_tag_relation','tag_id','course_id');
    }
    /*
     * 课程分类
     */
    public function category(){
        return $this->hasOne('BusinessCourseCategory','id','cid');
    }

    /*
     * 异步获取列表
     * @param $where
     * @return array
     */
    public static function CourseList($where){
        $data=($data=self::systemPage($where,true)->page((int)$where['page'],(int)$where['limit'])->select()) && count($data) ? $data->toArray() :[];
        foreach ($data as &$item){
           /* $item['like']=BusinessCourseRelation::where('course_id',$item['id'])->where('type','like')->count();
            $item['collect']=BusinessCourseRelation::where('course_id',$item['id'])->where('type','collect')->count();*/
            $item['catalog']=BusinessCourseCatalog::where('cid',$item['id'])->count();
            $item['category']=BusinessCourseCategory::get($item['cid'])?BusinessCourseCategory::get($item['cid'])->value('cate_name'):'';
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
        if($where['course_name'] != '')  $model = $model->where('course_name','LIKE',"%$where[course_name]%");
        if($isAjax===true){
            if(isset($where['order']) && $where['order']!=''){
                $model=$model->order(self::setOrder($where['order']));
            }
            return $model;
        }
        return self::page($model,function ($item){
            if($item['pid']){
                $item['pid_name'] = self::where('id',$item['pid'])->value('cate_name');
            }else{
                $item['pid_name'] = '顶级';
            }
        },$where);
    }
    public static function delCategory($id){
        return self::del($id);
    }

}