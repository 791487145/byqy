<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/25
 * Time: 9:09 AM
 */
namespace app\zhishi_routine\model\course;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseCommentReply extends ModelBasic{
    use ModelTrait;
   public function getMerchantReplyTimeAttr($value){
       return date('Y-m-d h:i:s',$value);
   }
   public static function getReplys($rep_id,$field='*'){
       $data['reply_list']=self::where('rep_id',$rep_id)->field($field)->select();
       $data['reply_count']=count($data['reply_list']);
       return $data;
   }
}