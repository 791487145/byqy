<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/20
 * Time: 9:41 AM
 */
namespace app\zhishi_routine\model\course;
use app\admin\model\system\SystemConfig;
use basic\ModelBasic;
use traits\ModelTrait;

class Course extends ModelBasic{
    use ModelTrait;
    public function getAddTimeAttr($value){
        return date('Y-m-d h:i:s',$value);
    }
    public function getAuthorAvatorAttr($value){
        return SystemConfig::getValue('site_url').$value;
    }

    /**
     * 精品课程
     * @param string $field
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBestCourse($field='*',$limit=0){
        $model=self::where('is_best',1)->where('is_show',1)
               ->field($field)->order('add_time DESC');
        if($limit) $model->limit($limit);
        return $model->select();
    }
    public static function getValidCourse($cid,$field='*'){
        return self::with('courseCatalog')->where('is_show',1)->field($field)->where('id',$cid)->find();
    }
    /*
     * 课程章节
     */
    public function courseCatalog(){
        return $this->hasMany('CourseCatalog','cid','id')->field('*');
    }

    public static function getCourseList($course_id)
    {
        $lists = self::where('id',$course_id)->find();
        $valid = $invalid = [];
        if(!$lists) return compact('valid','invalid');
        $lists = $lists->toArray();
        $lists['goods_num'] = 1;
        $valid  = $lists;
        return compact('valid','invalid');
    }
}