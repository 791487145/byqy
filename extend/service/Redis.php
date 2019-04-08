<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/1/29
 * Time: 1:18 PM
 */
namespace service;
class Redis extends \Redis{
    protected static $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ];
    public static function redis() {
        $con = new \Redis();
        $con->connect(self::$options['host'],self::$options['port'],self::$options['timeout']);
        return $con;
    }

}