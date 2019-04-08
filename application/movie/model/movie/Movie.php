<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/18
 */

namespace app\movie\model\movie;

use app\movie\model\store\StoreProduct;
use basic\ModelBasic;
use think\Request;
use traits\ModelTrait;

class Movie extends ModelBasic
{
    use ModelTrait;

    /**
     * @return $this
     */
    public static function validWhere(){
        return  self::where('is_show',1);
    }

    public function getAddTimeAttr($value){
        return date('Y-m-d',$value);
    }

        /**
         * 列表
     * @param string $field
     */
    public static function getList($field = '*',$data){
        $model = self::validWhere();
        if($data['title'] != '') $model = $model->where('title','like',"%$data[title]%");
        if($data['cid'] != '') $model = $model->where('cid',$data['cid']);
        $count = $model->count();
        $list = $model->field($field)->order('sort DESC')->limit($data['page']*$data['limit'],$data['limit'])->select()->toArray();
        return compact('count','list');
    }

    public static function getMovie($id,$user_id,$field = '*')
    {
        $list = self::with('store_product')->where('id',$id)->find();
        $list['collect_status'] = 0;
        $list['like_status'] = 0;
        self::where('id',$id)->setInc('visit');
        if(MovieLog::where('user_id',$user_id)->where('movie_id',$id)->where('action',1)->count()) $list['collect_status'] = 1;
        if(MovieLog::where('user_id',$user_id)->where('movie_id',$id)->where('action',2)->count()) $list['like_status'] = 1;
        return $list;
    }

    public function storeProduct()
    {
        return $this->belongsTo(StoreProduct::class,'goods_id','id')->field('id,image,store_name,store_info,price,sales,stock');
    }

}