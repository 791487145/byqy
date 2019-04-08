<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\movie;


use traits\ModelTrait;
use basic\ModelBasic;
use behavior\system\SystemBehavior;
use service\HookService;
use think\Session;

/**
 * Class SystemAdmin
 * @package app\admin\model\system
 */
class SystemAdmin extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    public static function setRolesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }



}