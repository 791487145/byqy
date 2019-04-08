<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/20
 * Time: 2:39 PM
 */
namespace app\movie\model\movie;
use basic\ModelBasic;
use traits\ModelTrait;

class MovieReply extends ModelBasic{
    use ModelTrait;

    public function getAddTimeAttr($value){
        return date('Y/m/d',$value);
    }

    public static function reply_list($where,$user_id)
    {
        $model = new self;
        if ($where['mv_id'] != '') $model = $model->where('r.mv_id', $where['mv_id']);
        $model = $model->alias('r')->join('__WECHAT_USER__ u', 'u.uid=r.uid');
        $model = $model->where('r.is_del', 0);
        $model = $model->field('r.*,u.nickname,u.headimgurl');
        $replies = $model->limit($where['page']*$where['limit'],$where['limit'])->select()->toArray();
        $count = $model->count();
        foreach ($replies as &$reply){
            $data = array(
                'user_id' => $user_id,
                'replay_id' => $reply['id']
            );
            $reply['user_praise_status'] = 0;
            if(MoviePraiseLog::where($data)->count()) $reply['user_praise_status'] = 1;
        }

        return compact('replies','count');
    }


}