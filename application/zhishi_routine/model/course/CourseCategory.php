<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/20
 * Time: 2:39 PM
 */
namespace app\zhishi_routine\model\course;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseCategory extends ModelBasic{
    use ModelTrait;
    public static function pidByCategory($pid,$field = '*',$limit = 0)
    {
        $model = self::where('pid',$pid)->where('is_show',1)->field($field);
        if($limit) $model->limit($limit);
        return $model->select();
    }
}