<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/25
 * Time: 10:29 AM
 */
namespace app\admin\model\movie;
use basic\ModelBasic;
use traits\ModelTrait;

class MovieReply extends ModelBasic{
    use ModelTrait;


    public static function systemPage($where)
    {
        $model = new self;
        if ($where['mv_id'] != '') $model = $model->where('r.mv_id', $where['mv_id']);
        if ($where['comment'] != '') $model = $model->where('r.comment', 'LIKE', "%$where[comment]%");
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