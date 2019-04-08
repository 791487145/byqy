<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/22
 * Time: 3:45 PM
 */
namespace app\zhishi_routine\model\course;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseReplyRelation extends ModelBasic{
    use ModelTrait;
    public static function getIdLike($id = 0){
        $count = self::where('rep_id',$id)->where('type','like')->count();
        return $count;
    }
    public static function isCourseRelation($rep_id,$uid,$relationType){
        $type=strtolower($relationType);
        return self::be(compact('rep_id','type','uid'));
    }
    public static function courseRelation($rep_id,$uid,$relationType){
        $type=strtolower($relationType);
        $data=compact('rep_id','type','uid');
        if(self::be($data)) return true;
        $data['add_time']=time();
        return self::set($data);
    }
    public static function unCourseRelation($rep_id,$uid,$relationType){
        if(!$rep_id) return self::setErrorInfo('评论不存在');
        $type=strtolower($relationType);
        return self::where(['uid'=>$uid,'type'=>$type,'rep_id'=>$rep_id])->delete();
    }
}