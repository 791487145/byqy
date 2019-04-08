<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/09
 */

namespace app\admin\model\store;


use basic\ModelBasic;
use traits\ModelTrait;

class StoreProductAttrResult extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['change_time'];

    protected static function setChangeTimeAttr($value)
    {
        return time();
    }

    protected static function setResultAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public static function setResult($result,$product_id)
    {
        $result = self::setResultAttr($result);
        $change_time = self::setChangeTimeAttr(0);
        return self::insert(compact('product_id','result','change_time'),true);
    }

    public static function getResult($productId)
    {
        return json_decode(self::where('product_id',$productId)->value('result'),true) ?: [];
    }

    public static function clearResult($productId)
    {
        return self::del($productId);
    }
    public static function decProductAttrStock($productId,$unique,$num){
        $productAttrValue=StoreProductAttrValue::where('unique',$unique)
            ->where('product_id',$productId)->field('product_id,cost,price,suk,stock')->find()->toArray();
        $productAttrResult=self::getResult($productId);
        $productAttrUnit=$productAttrResult['attr'][0]['value'];
        foreach ($productAttrResult['value'] as $k=>&$v){
            if($v['detail'][$productAttrUnit]==$productAttrValue['suk']){
                $v['sales']=$productAttrValue['stock'];
            }
        }
        self::clearResult($productId);
        return self::setResult($productAttrResult,$productId);
    }
}