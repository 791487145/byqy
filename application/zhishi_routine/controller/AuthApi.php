<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/15
 * Time: 4:06 PM
 */

namespace app\zhishi_routine\controller;
use app\admin\model\zhishi\Doctor;
use app\admin\model\zhishi\DoctorSection;
use app\routine\controller\AuthController;
use app\routine\model\store\StoreCouponUser;
use app\routine\model\user\User;
use app\routine\model\user\UserNotice;
use app\zhishi_routine\model\course\Course;
use app\zhishi_routine\model\course\CourseCatalog;
use app\zhishi_routine\model\course\CourseCategory;
use app\zhishi_routine\model\course\CourseCommentReply;
use app\zhishi_routine\model\course\CourseRelation;
use app\zhishi_routine\model\course\CourseReply;
use app\zhishi_routine\model\course\CourseReplyRelation;
use app\zhishi_routine\model\course\CourseTag;
use app\zhishi_routine\model\course\CourseTagRelation;
use app\zhishi_routine\model\order\CourseOrder;
use app\zhishi_routine\model\user\UserCourse;
use EasyWeChat\Js\Js;
use service\GroupDataService;
use service\JsonService;
use service\UtilService;
use think\Cache;
use think\Request;
use xavier\swoole\WebSocketFrame;
use think\Log;

class AuthApi extends AuthController
{
    /**
     * 首页
     * @return \think\response\Json
     */
    public function index(){
        $banner=GroupDataService::getData('routine_zhishi_home_banner')?:[];
        $menus=GroupDataService::getData('routine_zhishi_home_menus')?:[];
        $best=Course::getBestCourse('id,course_name,cid,pic,price,is_cost,browse,type',8);
        foreach ($best as $val){
            $val['status'] = 0;
            if(UserCourse::where('uid',$this->userInfo['uid'])->where('course_id',$val['id'])->count()){
                $val['status'] = 1;
            }
        }
        $data['banner']=$banner;
        $data['menus']=$menus;
        $data['best']=$best;
        return JsonService::successful($data);
    }
    /**
     * 课程搜索
     * @return \think\response\Json
     */
    public function search(Request $request){
        $name=$request->get('name');
        $model=new Course();
        $model->where('is_show',1);
        if($name) {
            $model->where('course_name','LIKE',"%$name%");
        }
        $list=$model->field('*')->select()->toArray();
        return JsonService::successful($list);
    }
    /**
     * 获取课程分类、标签
     * @return \think\response\Json
     */
    public function get_course_cate(){
        $course_cate=CourseCategory::pidByCategory(0,'id,cate_name')?:[];
        $course_tag=CourseTag::getList('id,tag_name')?:[];
        $data['course_cate']=$course_cate;
        $data['course_tag']=$course_tag;
        return JsonService::successful($data);
    }
    /**
     * 课程列表
     * @return \think\response\Json
     */
    public function get_course_list(Request $request){
        $data=UtilService::getMore([
            ['cid',0],
            ['tid',0],
            ['is_cost',0],
            ['page',0],
            ['limit',8]
        ],$request);
        $model=new Course();
        $where['is_show']=1;
        if($data['cid']){
           $where['cid']=$data['cid'];
        }
        if($data['tid']){
           $ids=CourseTagRelation::where('tag_id',$data['tid'])->column('course_id');
           $where['id']=['IN',$ids];
        }
        if($data['is_cost']){
            $where['is_cost']=$data['is_cost'];
        }
        $count=$model->where($where)->count();
        $list=$model->where($where)->field('id,course_name,cid,pic,is_cost,price,browse,type')->limit($data['page']*$data['limit'],$data['limit'])->select();
        foreach ($list as $value){
            $value['status'] = 0;
            if(UserCourse::where('uid',$this->userInfo['uid'])->where('course_id',$value['id'])->count()){
                $value['status'] = 1;
            }
        }
        $res["count"] = $count;
        $res['course_lit']=$list;
        return JsonService::successful($res);
    }
    /**
     * 课程详情
     * @return \think\response\Json
     */
    public function course_details(Request $request){
        $data=UtilService::postMore(['id'],$request);
        $id=$data['id'];
        if(!$id||!$courseInfo=Course::getValidCourse($id)) return JsonService::fail('课程不存在或已经下架');
        $courseInfo->setInc('browse',1);
        $courseInfo['status'] = 0;
        if(UserCourse::where('uid',$this->userInfo['uid'])->where('course_id',$courseInfo['id'])->count()){
            $courseInfo['status'] = 1;
        }
        $courseInfo['userCollect']=CourseRelation::isCourseRelation($id,$this->userInfo['uid'],'collect');
        $courseInfo['userLike']=CourseRelation::isCourseRelation($id,$this->userInfo['uid'],'like');
        $courseInfo['like_count']=CourseRelation::getIdLike($id);
        return JsonService::successful($courseInfo);
    }
    /**
     * 收藏课程
     * @return \think\response\Json
     */
    public function collect_course(Request $request){
        $data=UtilService::postMore(['id'],$request);
        $id=$data['id'];
        if(!$id) return JsonService::fail('参数错误');
        $res=CourseRelation::courseRelation($id,$this->userInfo['uid'],'collect');
        if(!$res) return JsonService::fail('收藏失败');
        return JsonService::successful('收藏成功');
    }
    /**
     * 取消收藏课程
     * @return \think\response\Json
     */
    public function unCollect_course(Request $request){
        $data=UtilService::postMore(['id'],$request);
        $id=$data['id'];
        if(!$id) return JsonService::fail('参数错误');
        $res=CourseRelation::unCourseRelation($id,$this->userInfo['uid'],'collect');
        if(!$res) return JsonService::fail('取消收藏失败');
        return JsonService::successful('取消收藏成功');
    }
    /**
     * 点赞课程
     * @return \think\response\Json
     */
    public function like(Request $request){
        $data=UtilService::postMore(['id'],$request);
        $id=$data['id'];
        if(!$id) return JsonService::fail('参数错误');
        $res=CourseRelation::courseRelation($id,$this->userInfo['uid'],'like');
        if(!$res) return JsonService::fail('点赞失败');
        return JsonService::successful('点赞成功');
    }
    /**
     * 取消点赞课程
     * @return \think\response\Json
     */
    public function unLike(Request $request){
        $data=UtilService::postMore(['id'],$request);
        $id=$data['id'];
        if(!$id) return JsonService::fail('参数错误');
        $res=CourseRelation::unCourseRelation($id,$this->userInfo['uid'],'like');
        if(!$res) return JsonService::fail('取消点赞失败');
        return JsonService::successful('取消点赞成功');
    }
    /**
     * 章节详情
     * @return \think\response\Json
     */
    public function catalog_details(Request $request){
        $data=UtilService::postMore(['id'],$request);
        $id=$data['id'];
        if(!$id||!$catalogInfo=CourseCatalog::where('id',$id)->find()) return JsonService::fail('参数错误或章节不存在');
        $catalogInfo->setInc('browse',1);
        $catalogInfo['status'] = 0;
        if(UserCourse::where('uid',$this->userInfo['uid'])->where('course_id',$catalogInfo['cid'])->count()){
            $catalogInfo['status'] = 1;
        }
        $catalogInfo['prev']=CourseCatalog::prevCatalog($catalogInfo)?:'';
        $catalogInfo['next']=CourseCatalog::nextCatalog($catalogInfo)?:'';

        return JsonService::successful($catalogInfo);

    }
    /**
     * 课程评论
     * @return \think\response\Json
     */
    public function get_course_comments(Request $request){
        $data=UtilService::postMore([
            'cid',
            ['page',0],
            ['limit',8],
        ],$request);
        $cid=$data['cid'];
        if(!$cid) return JsonService::fail('参数错误');
        $commits=CourseReply::getComments($cid,'*',$data['page'],$data['limit']);
        foreach ($commits['list'] as &$list){
            //评论人
            $list['user']=($nickname=User::where('uid',$list['uid'])->value('nickname'))?$nickname:"匿名";
            $list['user_avatar']=User::where('uid',$list['uid'])->value('avatar');
            //是否点赞
            $list['userLike']=CourseReplyRelation::isCourseRelation($list['id'],$this->userInfo['uid'],'like');
            //点赞数
            $list['likeCount']=CourseReplyRelation::getIdLike($list['id']);
            //评论回复
            $list['reply_list']=CourseCommentReply::getReplys($list['id'],'merchant_name,merchant_reply_content,merchant_reply_time');
        }
        return JsonService::successful($commits);

    }
    /**
     * 评论点赞
     * @return \think\response\Json
     */
    public function comment_like(Request $request){
        $data=UtilService::postMore(['rid'],$request);
        $rid=$data['rid'];
        if(!$rid||!CourseReply::find(['id'=>$rid])) return JsonService::fail('参数错误或评论不存在');
        if(!CourseReplyRelation::courseRelation($rid,$this->userInfo['uid'],'like')) return JsonService::fail('点赞失败');
        return JsonService::successful('点赞成功');
    }
    /**
     * 取消评论点赞
     * @return \think\response\Json
     */
    public function unComment_like(Request $request){
        $data=UtilService::postMore(['rid'],$request);
        $rid=$data['rid'];
        if(!$rid||!CourseReply::find(['id'=>$rid])) return JsonService::fail('参数错误');
        if(!CourseReplyRelation::unCourseRelation($rid,$this->userInfo['uid'],'like')) return JsonService::fail('取消点赞失败');
        return JsonService::successful('取消点赞成功');
    }
    /**
     * 添加课程评论
     * @return \think\response\Json
     */
    public function add_comment(Request $request){
        $data=UtilService::postMore([
            'uid',
            'comment',
            'cid',
        ],$request);
        if(!$data['uid']) return JsonService::fail('用户id为空');
        if(!$data['comment']) return JsonService::fail('评论不能为空');
        if(!$data['cid']) return JsonService::fail('课程id不能为空');
        if(!CourseReply::checkTimeReply($data['uid'],$data['cid'])) return JsonService::fail('请勿频繁评论，1分钟后尝试');
        $data['add_time']=time();
        if(!CourseReply::set($data)) return JsonService::fail('评论失败');
        return JsonService::successful('评论成功');
    }
    /**
     * 用户公告
     * @param int $page
     * @param int $limit
     * @return \think\response\Json
     */
    public function get_notice_list($page = 0, $limit = 8)
    {
        $list = UserNotice::getNoticeList1($this->userInfo['uid'],$page,$limit);
        return JsonService::successful($list);
    }
    /**
     * 个人中心
     * @return \think\response\Json
     */
    public function my(){
        $this->userInfo['collect_count']=CourseRelation::getUserIdCollect($this->userInfo['uid']);
        $this->userInfo['course_count']=UserCourse::getUserIdCourse($this->userInfo['uid']);
        return JsonService::successful($this->userInfo);
    }
    /**
     * 个人收藏课程
     * @return \think\response\Json
     */
    public function get_user_collect_course($page = 0,$limit = 8){
        $list['count']=CourseRelation::where('A.uid',$this->userInfo['uid'])
            ->alias('A')->where('B.is_show',1)
            ->where('A.type','collect')->order('A.add_time DESC')
            ->join('__COURSE__ B','A.course_id = B.id','right')
            ->count();
        $list['course_list']=CourseRelation::where('A.uid',$this->userInfo['uid'])
            ->field('B.id,B.course_name,B.browse,B.pic,B.type')->alias('A')->where('B.is_show',1)
            ->where('A.type','collect')->order('A.add_time DESC')
            ->join('__COURSE__ B','A.course_id = B.id','right')
            ->limit(($page*$limit),$limit)->select()->toArray();
        return JsonService::successful($list);
    }

    /**
     * 科室
     */
    public function doctor_section()
    {
        $doctor_sections = DoctorSection::select()->toArray();
        return JsonService::successful($doctor_sections);
    }

    /**
     * 医生列表
     */
    public function doctor_list($first = 0,$limit = 8)
    {
        $request = Request::instance();
        $lists = $request->post();
        $sid = isset($lists['sid']) ? $lists['sid']: 0;
        $doctor_name = isset($lists['doctor_name']) ? $lists['doctor_name'] : '';

        $where = array(
            'doctor_name' => $doctor_name,
            'sid' => $sid,
            'limit' => $limit,
            'page' => $first
        );

        $doctors = Doctor::DoctorList($where);
        return JsonService::successful($doctors);
    }
    //医生详情
    public function doctor_detail()
    {
        $request = Request::instance();
        if(!$doctor_id = $request->post('doctor_id','')){
            return JsonService::fail('参数错误');
        };

        $doctor = Doctor::where('id',$doctor_id)->find();
        return JsonService::successful($doctor);
    }

    /**
     * 订单页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function confirm_order(Request $request){
        $data = UtilService::postMore(['courseId'],$request);
        $courseId = $data['courseId'];
        if(!is_string($courseId) || !$courseId ) return JsonService::fail('请提交购买的课程');
        if(UserCourse::where('course_id',$courseId)->where('uid',$this->userInfo['uid'])->count()){
            return JsonService::fail('您已购买该课程，请勿重复购买');
        }
        $course = Course::getCourseList($courseId);
        if(!$course['valid']) return JsonService::fail('请提交购买的课程');
        $course_info = $course['valid'];
        $priceCourse = CourseOrder::getOrderPriceGroup($course_info);

        $data['orderKey'] = CourseOrder::cacheOrderInfo($this->userInfo['uid'],$course_info,$priceCourse);
        $data['course_info'] = $course_info;
        $data['priceCourse'] = $priceCourse;

        return JsonService::successful($data);
    }

    /**
     * 创建订单
     * @param string $key
     * @return \think\response\Json
     */
    public function create_order($key = '')
    {
        if(!$key) return JsonService::fail('参数错误!');
        if(CourseOrder::be(['order_id|unique'=>$key,'uid'=>$this->userInfo['uid'],'is_del'=>0]))
            return JsonService::status('extend_order','订单已生成',['orderId'=>$key,'key'=>$key]);
        list($payType,$useIntegral,$mark,$combinationId,$pinkId,$seckill_id,$formId,$bargainId) = UtilService::postMore([
            'payType','useIntegral','mark',['combinationId',0],['pinkId',0],['seckill_id',0],['formId',''],['bargainId','']
        ],Request::instance(),true);
        $payType = strtolower($payType);

        $order = CourseOrder::cacheKeyCreateOrder($this->userInfo['uid'],$key,$payType,$useIntegral,$mark,$combinationId,$pinkId,$seckill_id,$bargainId);

        $orderId = $order['order_id'];
        $info = compact('orderId','key');
        if($orderId){
            if($payType == 'weixin'){
                $orderInfo = CourseOrder::where('order_id',$orderId)->find();
                if(!$orderInfo || !isset($orderInfo['paid'])) exception('支付订单不存在!');
                if($orderInfo['paid']) exception('支付已支付!');
                if(bcsub((float)$orderInfo['pay_price'],0,2) <= 0){
                    if(CourseOrder::jsPayPrice($orderId,$this->userInfo['uid'],$formId))
                        return JsonService::status('success','微信支付成功',$info);
                    else
                        return JsonService::status('pay_error',StoreOrder::getErrorInfo());
                }else{
                    try{
                        $jsConfig = CourseOrder::jsPay($orderId);
                    }catch (\Exception $e){
                        return JsonService::status('pay_error',$e->getMessage(),$info);
                    }
                    $info['jsConfig'] = $jsConfig;
                    return JsonService::status('wechat_pay','订单创建成功',$info);
                }
            }else if($payType == 'yue'){
                if(CourseOrder::yuePay($orderId,$this->userInfo['uid'],$formId))
                    return JsonService::status('success','余额支付成功',$info);
                else
                    return JsonService::status('pay_error',StoreOrder::getErrorInfo());
            }else if($payType == 'offline'){
                return JsonService::status('success','订单创建成功',$info);
            }
        }else{
            return JsonService::fail(CourseOrder::getErrorInfo('订单生成失败!'));
        }
    }

    /**
     * 获取订单列表
     * @param string $type
     * @param int $first
     * @param int $limit
     * @param string $search
     * @return \think\response\Json
     */
    public function get_user_course_order_list($type = '',$first = 0, $limit = 8,$search = '')
    {
        $course_ids = UserCourse::where('uid',$this->userInfo['uid'])->column('course_id');
        $list['count'] = count($course_ids);
        $list['course_list']=Course::where('id','in',$course_ids)->select()->toArray();
        return JsonService::successful($list);
    }
}