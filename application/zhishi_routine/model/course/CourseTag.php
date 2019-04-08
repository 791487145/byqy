<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/20
 * Time: 2:42 PM
 */
namespace app\zhishi_routine\model\course;
use basic\ModelBasic;
use traits\ModelTrait;

class  CourseTag extends ModelBasic
{
    use ModelTrait;
    public static function getList($field='*'){
        $model=self::where('is_show',1)->field($field)->order('sort');
        return $model->select();
    }
}