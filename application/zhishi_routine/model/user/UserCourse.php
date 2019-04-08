<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/21
 */

namespace app\zhishi_routine\model\user;

use app\routine\model\routine\RoutineQrcode;
use basic\ModelBasic;
use traits\ModelTrait;
use app\routine\model\user\User;
use app\routine\model\user\WechatUser;
class UserCourse extends ModelBasic
{
    use ModelTrait;

    public static function getUserIdCourse($uid = 0){
        $count = self::where('uid',$uid)->count();
        return $count;
    }

}