<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/26
 */

namespace app\zhishi_routine\model\order;


use basic\ModelBasic;
use traits\ModelTrait;

class CourseOrderCartInfo extends ModelBasic
{
    use ModelTrait;

    public static function setCartInfo($oid,array $cartInfo)
    {
        //$group = [];

        $group[] = [
            'oid'=>$oid,
            'product_id'=>$cartInfo['id'],
            'cart_info'=>json_encode($cartInfo),
            'unique'=>md5($cartInfo['id'].''.$oid)
        ];

        return self::setAll($group);
    }

}