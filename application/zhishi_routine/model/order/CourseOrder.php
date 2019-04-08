<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/20
 * Time: 9:41 AM
 */
namespace app\zhishi_routine\model\order;
use app\admin\model\system\SystemConfig;
use app\routine\model\user\User;
use app\routine\model\user\UserBill;
use app\routine\model\user\WechatUser;
use basic\ModelBasic;
use behavior\routine\StoreProductBehavior;
use service\HookService;
use service\RoutineService;
use service\SystemConfigService;
use traits\ModelTrait;
use think\Cache;

class CourseOrder extends ModelBasic{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected static $payType = ['weixin'=>'微信支付','yue'=>'余额支付','offline'=>'线下支付'];

    protected static $deliveryType = ['send'=>'商家配送','express'=>'快递配送'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    public function course_cart_info()
    {
        return $this->belongsTo(CourseOrderCartInfo::class,'oid','id');
    }

    //订单总价
    public static function getOrderPriceGroup($course_info)
    {
        $totalPrice = self::getOrderTotalPrice($course_info);

        return compact('totalPrice');
    }
    //获取订单总价
    public static function getOrderTotalPrice($course_info)
    {
        $totalPrice = $course_info['price'];
        return $totalPrice;
    }
    //缓存订单临时id
    public static function cacheOrderInfo($uid,$cartInfo,$priceCourse,$other = [],$cacheTime = 600)
    {
        $key = md5(time());
        Cache::set('user_course_order_'.$uid.$key,compact('cartInfo','priceCourse','other'),$cacheTime);
        return $key;
    }
    //获取订单临时id
    public static function getCacheOrderInfo($uid,$key)
    {
        $cacheName = 'user_course_order_'.$uid.$key;
        if(!Cache::has($cacheName)) return null;
        return Cache::get($cacheName);
    }

    public static function clearCacheOrderInfo($uid,$key)
    {
        Cache::clear('user_course_order_'.$uid.$key);
    }

    /**
     *生成订单id
     */
    public static function getNewOrderId()
    {
        $count = (int) self::where('add_time',['>=',strtotime(date("Y-m-d"))],['<',strtotime(date("Y-m-d",strtotime('+1 day')))])->count();
        return 'wx'.date('YmdHis',time()).(10000+$count+1);
    }

    public static function cacheKeyCreateOrder($uid,$key,$payType,$useIntegral = false,$mark = '',$combinationId = 0,$pinkId = 0,$seckill_id=0,$bargain_id=0)
    {
        if(!array_key_exists($payType,self::$payType)) return self::setErrorInfo('选择支付方式有误!');
        if(self::be(['unique'=>$key,'uid'=>$uid])) return self::setErrorInfo('请勿重复提交订单');
        $userInfo = User::getUserInfo($uid);
        if(!$userInfo) return  self::setErrorInfo('用户不存在!');
        $cartGroup = self::getCacheOrderInfo($uid,$key);
        if(!$cartGroup) return self::setErrorInfo('订单已过期,请刷新当前页面!');
        $cartInfo = $cartGroup['cartInfo'];
        $priceCourse = $cartGroup['priceCourse'];
        $other = $cartGroup['other'];
        $payPrice = $priceCourse['totalPrice'];
        //$payPostage = $priceGroup['storePostage'];


        //是否包邮
       /* if((isset($other['offlinePostage'])  && $other['offlinePostage'] && $payType == 'offline')) $payPostage = 0;
        $payPrice = bcadd($payPrice,$payPostage,2);*/

        //积分抵扣
       /* $res2 = true;
        if($useIntegral && $userInfo['integral'] > 0){
            $deductionPrice = bcmul($userInfo['integral'],$other['integralRatio'],2);
            if($deductionPrice < $payPrice){
                $payPrice = bcsub($payPrice,$deductionPrice,2);
                $usedIntegral = $userInfo['integral'];
                $res2 = false !== User::edit(['integral'=>0],$userInfo['uid'],'uid');
            }else{
                $deductionPrice = $payPrice;
                $usedIntegral = bcdiv($payPrice,$other['integralRatio'],2);
                $res2 = false !== User::bcDec($userInfo['uid'],'integral',$usedIntegral,'uid');
                $payPrice = 0;
            }
            $res2 = $res2 && false != UserBill::expend('积分抵扣',$uid,'integral','deduction',$usedIntegral,$key,$userInfo['integral'],'购买商品使用'.floatval($usedIntegral).'积分抵扣'.floatval($deductionPrice).'元');
        }else{
            $deductionPrice = 0;
            $usedIntegral = 0;
        }
        if(!$res2) return self::setErrorInfo('使用积分抵扣失败!');*/

        $courseId = array($cartInfo['id']);
        $totalNum = $cartInfo['goods_num'];
        $gainIntegral = 0;

        $orderInfo = [
            'uid'=>$uid,
            'order_id'=>self::getNewOrderId(),
            'real_name'=>$userInfo['nickname'],
            'course_id'=>$courseId,
            'total_num'=>$totalNum,
            'total_price'=>$priceCourse['totalPrice'],
            'pay_price'=>$payPrice,
            'paid'=>0,
            'pay_type'=>$payType,
            'course_id' => $cartInfo['id'],
            'gain_integral'=>$gainIntegral,
            'mark'=>htmlspecialchars($mark),
            'combination_id'=>$combinationId,
            'pink_id'=>$pinkId,
            'seckill_id'=>$seckill_id,
            'bargain_id'=>$bargain_id,
            'is_channel'=>1,
            'unique'=>$key,
        ];
        $order = self::set($orderInfo);
        if(!$order)return self::setErrorInfo('订单生成失败!');
        $res5 = true;

        //保存购物车商品信息
        $res4 = false !== CourseOrderCartInfo::setCartInfo($order['id'],$cartInfo);
       
        if(!$res4 || !$res5 ) return self::setErrorInfo('订单生成失败!');
        try{
           // HookService::listen('course_product_order_create',$order,compact('cartInfo','addressId'),false,StoreProductBehavior::class);
        }catch (\Exception $e){
            return self::setErrorInfo($e->getMessage());
        }
        self::clearCacheOrderInfo($uid,$key);
        self::commitTrans();
        CourseOrderStatus::status($order['id'],'cache_key_create_order','订单生成');
        return $order;
    }

    /**
     * 微信支付 为 0元
     */
    public static function jsPayPrice($order_id,$uid,$formId = ''){
        $orderInfo = self::where('uid',$uid)->where('order_id',$order_id)->where('is_del',0)->find();
        if(!$orderInfo) return self::setErrorInfo('订单不存在!');
        if($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
        $userInfo = User::getUserInfo($uid);
        self::beginTrans();
        $res1 = UserBill::expend('购买课程',$uid,'now_money','pay_product',$orderInfo['pay_price'],$orderInfo['id'],$userInfo['now_money'],'微信支付'.floatval($orderInfo['pay_price']).'元购买课程');
        $res2 = self::paySuccess($order_id,$formId);
        $res = $res1 && $res2;
        self::checkTrans($res);
        return $res;
    }

    public static function jsPay($orderId,$field = 'order_id')
    {
        if(is_string($orderId))
            $orderInfo = self::where($field,$orderId)->find();
        else
            $orderInfo = $orderId;
        if(!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
        if($orderInfo['paid']) exception('支付已支付!');
        if($orderInfo['pay_price'] <= 0) exception('该支付无需支付!');
        $openid = WechatUser::getOpenId($orderInfo['uid']);
        return RoutineService::payZhiShiRoutine($openid,$orderInfo['order_id'],$orderInfo['pay_price'],'course',SystemConfigService::get('site_name'));
    }

    /**
     * //TODO 支付成功后
     */
    public static function paySuccess($orderId,$formId = '')
    {
        $order = self::where('order_id',$orderId)->find();
        $resPink = true;
        $res1 = self::where('order_id',$orderId)->update(['paid'=>1,'pay_time'=>time()]);
        User::bcInc($order['uid'],'pay_count',1,'uid');
        if($order->combination_id && $res1 && !$order->refund_status) $resPink = StorePink::createPink($order);//创建拼团
        $oid = self::where('order_id',$orderId)->value('id');
        StoreOrderStatus::status($oid,'pay_success','用户付款成功');
        RoutineTemplate::sendOrderSuccess($formId,$orderId);
        $res = $res1 && $resPink;
        return false !== $res;
    }

    //yue支付
    public static function yuePay($order_id,$uid,$formId = '')
    {
        $orderInfo = self::where('uid',$uid)->where('order_id',$order_id)->where('is_del',0)->find();
        if(!$orderInfo) return self::setErrorInfo('订单不存在!');
        if($orderInfo['paid']) return self::setErrorInfo('该订单已支付!');
        if($orderInfo['pay_type'] != 'yue') return self::setErrorInfo('该订单不能使用余额支付!');
        $userInfo = User::getUserInfo($uid);
        if($userInfo['now_money'] < $orderInfo['pay_price'])
            return self::setErrorInfo('余额不足'.floatval($orderInfo['pay_price']));
        self::beginTrans();
        $res1 = false !== User::bcDec($uid,'now_money',$orderInfo['pay_price'],'uid');
        $res2 = UserBill::expend('购买商品',$uid,'now_money','pay_product',$orderInfo['pay_price'],$orderInfo['id'],$userInfo['now_money'],'余额支付'.floatval($orderInfo['pay_price']).'元购买商品');
        $res3 = self::paySuccess($order_id,$formId);
        try{
//            HookService::listen('yue_pay_product',$userInfo,$orderInfo,false,PaymentBehavior::class);
        }catch (\Exception $e){
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
        $res = $res1 && $res2 && $res3;
        self::checkTrans($res);
        return $res;
    }

    public static function getUserOrderList($uid,$status = '',$first = 0,$limit = 8,$order_id = null)
    {
        $list = self::statusByWhere($status)->where('is_del',0)->where('uid',$uid);
        if(!empty($order_id)){
            $list = $list->where('id','in',$order_id);
        }

        $list = $list->field('seckill_id,bargain_id,combination_id,id,order_id,pay_price,total_num,total_price,paid,status,refund_status,pay_type,coupon_price,deduction_price,pink_id')
            ->order('add_time DESC')->limit($first,$limit)->select()->toArray();

        foreach ($list as $k=>$order){
            $list[$k] = self::tidyOrder($order,true);
        }

        return $list;
    }

    /**
     * 订单商品详细
     * @param $order
     * @param bool $detail
     * @return mixed
     * @throws \think\Exception
     */
    public static function tidyOrder($order,$detail = false)
    {
        if($detail == true && isset($order['id'])){
            $cartInfo = self::getDb('CourseOrderCartInfo')->where('oid',$order['id'])->column('cart_info','unique')?:[];
            $num = 0;
            foreach ($cartInfo as $k=>$cart){
                $cartInfo[$k] = json_decode($cart, true);
                $num = $num + $cartInfo[$k]['goods_num'];
                $cartInfo[$k]['unique'] = $k;
            }
            $order['cartInfo'] = $cartInfo;
            $order['order_num'] = $num;//订单商品总数
        }

        $status = [];
        if(!$order['paid'] && $order['pay_type'] == 'offline' && !$order['status'] >= 2){
            $status['_type'] = 9;
            $status['_title'] = '线下付款';
            $status['_msg'] = '等待商家处理,请耐心等待';
            $status['_class'] = 'nobuy';
        }else if(!$order['paid']){
            $status['_type'] = 0;
            $status['_title'] = '未支付';
            $status['_msg'] = '立即支付订单吧';
            $status['_class'] = 'nobuy';
        }else if($order['refund_status'] == 1){
            $status['_type'] = -1;
            $status['_title'] = '申请退款中';
            $status['_msg'] = '商家审核中,请耐心等待';
            $status['_class'] = 'state-sqtk';
        }else if($order['refund_status'] == 2){
            $status['_type'] = -2;
            $status['_title'] = '已退款';
            $status['_msg'] = '已为您退款,感谢您的支持';
            $status['_class'] = 'state-sqtk';
        }else if(!$order['status']){
            if($order['pink_id']){
                if(StorePink::where('id',$order['pink_id'])->where('status',1)->count()){
                    $status['_type'] = 1;
                    $status['_title'] = '拼团中';
                    $status['_msg'] = '等待其他人参加拼团';
                    $status['_class'] = 'state-nfh';
                }else{
                    $status['_type'] = 1;
                    $status['_title'] = '未发货';
                    $status['_msg'] = '商家未发货,请耐心等待';
                    $status['_class'] = 'state-nfh';
                }
            }else{
                $status['_type'] = 1;
                $status['_title'] = '未发货';
                $status['_msg'] = '商家未发货,请耐心等待';
                $status['_class'] = 'state-nfh';
            }
        }else if($order['status'] == 1){
            $status['_type'] = 2;
            $status['_title'] = '待收货';
            $status['_msg'] = date('m月d日H时i分',StoreOrderStatus::getTime($order['id'],'delivery_goods')).'服务商已发货';
            $status['_class'] = 'state-ysh';
        }else if($order['status'] == 2){
            $status['_type'] = 3;
            $status['_title'] = '待评价';
            $status['_msg'] = '已收货,快去评价一下吧';
            $status['_class'] = 'state-ypj';
        }else if($order['status'] == 3){
            $status['_type'] = 4;
            $status['_title'] = '交易完成';
            $status['_msg'] = '交易完成,感谢您的支持';
            $status['_class'] = 'state-ytk';
        }
        if(isset($order['pay_type']))
            $status['_payType'] = isset(self::$payType[$order['pay_type']]) ? self::$payType[$order['pay_type']] : '其他方式';
        if(isset($order['delivery_type']))
            $status['_deliveryType'] = isset(self::$deliveryType[$order['delivery_type']]) ? self::$deliveryType[$order['delivery_type']] : '其他方式';
        $order['_status'] = $status;
        return $order;
    }

    public static function statusByWhere($status,$model = null)
    {
        if($model == null) $model = new self;
        if('' === $status)
            return $model;
        else if($status == 0)
            return $model->where('paid',0)->where('status',0)->where('refund_status',0);
        else if($status == 1)//待发货
            return $model->where('paid',1)->where('status',0)->where('refund_status',0);
        else if($status == 2)
            return $model->where('paid',1)->where('status',1)->where('refund_status',0);
        else if($status == 3)
            return $model->where('paid',1)->where('status',2)->where('refund_status',0);
        else if($status == 4)
            return $model->where('paid',1)->where('status',3)->where('refund_status',0);
        else if($status == -1)
            return $model->where('paid',1)->where('refund_status',1);
        else if($status == -2)
            return $model->where('paid',1)->where('refund_status',2);
        else if($status == -3)
            return $model->where('paid',1)->where('refund_status','IN','1,2');
        else
            return $model;
    }
}