<?php
namespace app\zhishi_routine\controller;
use app\routine\model\user\User;
use app\zhishi_routine\model\order\CourseOrder;
use app\zhishi_routine\model\order\CourseOrderStatus;
use app\zhishi_routine\model\user\UserCourse;
use behavior\wechat\PaymentBehavior;
use service\HookService;
use service\RoutineNotify;
use think\Cache;
use think\Log;

/**
 * 小程序支付回调
 * Class Routine
 * @package app\routine\controller
 */
class Routine
{
    /**
     *   支付  异步回调
     */
    public function notify()
    {
        $result = RoutineNotify::course_notify();
        $time = time();
        $course_order = CourseOrder::where('order_id',trim($result['out_trade_no']))->find();
        $param = array(
            'id'=>$course_order['id'],
            'status' => 3,
            'pay_time' => $time,
            'paid' => 1
        );
        $data = array(
            'oid' => $course_order['id'],
            'change_type' => 'cache_key_order_complate',
            'change_message' => '订单完成',
            'change_time' => $time
        );

        $data2 = array(
            'uid' => $course_order['uid'],
            'course_id' => $course_order['course_id'],
            'order_id' => $course_order['id']
        );

        CourseOrderStatus::set($data);
        $course_order->update($param);
        UserCourse::set($data2);
        User::where('uid',$course_order['uid'])->setInc('pay_count');
        //return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }
}


