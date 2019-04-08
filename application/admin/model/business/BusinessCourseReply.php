<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/25
 * Time: 10:29 AM
 */
namespace app\admin\model\business;
use basic\ModelBasic;
use traits\ModelTrait;

class BusinessCourseReply extends ModelBasic{
    use ModelTrait;

    public function replys(){
        return $this->hasMany('BusinessCourseCommentReply','rep_id','id');
    }
    public static function systemPage($where)
    {
        $model = new self;

        if($where['cid']!='') $model->where('cid',$where['cid']);
        if ($where['comment'] != '') $model = $model->where('r.comment', 'LIKE', "%$where[comment]%");
        if ($where['is_reply'] != '') {
            if ($where['is_reply'] >= 0) {
                $model = $model->where('r.is_reply', $where['is_reply']);
            } else {
                $model = $model->where('r.is_reply', 'GT', 0);
            }
        }
        $model = $model->with('replys');
        $model = $model->alias('r')->join('__WECHAT_USER__ u', 'u.uid=r.uid');
        $model = $model->where('r.is_del', 0);
        $model = $model->field('r.*,u.nickname,u.headimgurl');
        return self::page($model, function ($itme) {
        }, $where);
    }
    public static function delCategory($id){
        return self::del($id);
    }
}