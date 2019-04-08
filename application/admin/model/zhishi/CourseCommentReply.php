<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/25
 * Time: 10:26 AM
 */
namespace app\admin\model\zhishi;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseCommentReply extends ModelBasic{
    use ModelTrait;
    public static function systemPage($where)
    {
        $model = new self;
        if ($where['comment'] != '') $model = $model->where('r.comment', 'LIKE', "%$where[comment]%");
        if ($where['is_reply'] != '') {
            if ($where['is_reply'] >= 0) {
                $model = $model->where('r.is_reply', $where['is_reply']);
            } else {
                $model = $model->where('r.is_reply', 'GT', 0);
            }
        }
        if ($where['product_id']) $model = $model->where('r.product_id', $where['product_id']);
        $model = $model->alias('r')->join('__WECHAT_USER__ u', 'u.uid=r.uid');
        $model = $model->join('__STORE_PRODUCT__ p', 'p.id=r.product_id');
        $model = $model->where('r.is_del', 0);
        $model = $model->field('r.*,u.nickname,u.headimgurl,p.store_name');
        return self::page($model, function ($itme) {

        }, $where);
    }
}