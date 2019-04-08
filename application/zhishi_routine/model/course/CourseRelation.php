<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/19
 * Time: 3:45 PM
 */
namespace app\zhishi_routine\model\course;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseRelation extends ModelBasic{
    use ModelTrait;
    /**
     * 获取用户收藏所有产品的个数
     * @param $uid
     * @return int|string
     */
    public static function getUserIdCollect($uid = 0){
        $count = self::where('uid',$uid)->where('type','collect')->count();
        return $count;
    }
    public static function getIdLike($cid){
        $count = self::where('course_id',$cid)->where('type','like')->count();
        return $count;
    }
    public static function isCourseRelation($course_id,$uid,$relationType){
        $type=strtolower($relationType);
        return self::be(compact('course_id','type','uid'));
    }
    public static function courseRelation($course_id,$uid,$relationType){
        $type=strtolower($relationType);
        $data=compact('course_id','type','uid');
        if(self::be($data)) return true;
        $data['add_time']=time();
        return self::set($data);
    }
    public static function unCourseRelation($course_id,$uid,$relationType){
        if(!$course_id) return self::setErrorInfo('课程不存在');
        $type=strtolower($relationType);
        return self::where(['uid'=>$uid,'type'=>$type,'course_id'=>$course_id])->delete();
    }
}