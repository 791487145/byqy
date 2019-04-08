<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/22
 * Time: 3:18 PM
 */
namespace app\zhishi_routine\model\course;

use app\routine\model\user\User;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseReply extends ModelBasic{
    use ModelTrait;
    public function getAddTimeAttr($value){
        return date('Y-m-d h:i:s',$value);
    }
    public static function getComments($cid,$field='*',$page=0,$limit=8){
        $data['list']=self::where('cid',$cid)->field($field)->limit($page*$limit,$limit)->select()->toArray();
        $data['count']=self::where('cid',$cid)->count();
        return $data;
    }
    public static function checkTimeReply($uid,$cid){
        $oldTime=self::where(['uid'=>$uid,'cid'=>$cid])->order('add_time DESC')->value('add_time');
        if($oldTime<time()&&$oldTime>time()-60) return false;
        return true;
    }
}