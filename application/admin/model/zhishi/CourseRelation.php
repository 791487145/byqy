<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 4:21 PM
 */
namespace app\admin\model\zhishi;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseRelation extends ModelBasic{
    use ModelTrait;
    public static function getCollect($cid){
        $model = new self();
        $model = $model->where('r.course_id',$cid)->where('r.type','collect');
        $model = $model->alias('r')->join('__WECHAT_USER__ u','u.uid=r.uid');
        $model = $model->field('r.*,u.nickname');
        return self::page($model);
    }
    public static function getLike($cid){
        $model = new self();
        $model = $model->where('r.course_id',$cid)->where('r.type','like');
        $model = $model->alias('r')->join('__WECHAT_USER__ u','u.uid=r.uid');
        $model = $model->field('r.*,u.nickname');
        return self::page($model);
    }
}