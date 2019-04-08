<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/25
 * Time: 9:25 AM
 */
namespace app\admin\controller\business;
use app\admin\controller\AuthController;
use app\admin\model\business\BusinessCourseCommentReply;
use app\admin\model\business\BusinessCourseReply;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
class BusinessCourseComment extends AuthController{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $where=Util::getMore([
            ['cid',$this->request->param('cid')],
            ['is_reply',$this->request->param('is_reply')],
            ['comment',''],
        ],$this->request);
        $comments=BusinessCourseReply::systemPage($where);
        $this->assign('where',$where);
        $this->assign($comments);
        return $this->fetch();
    }
    /**
     * 回复评论
     *
     * @return \think\Response
     */
    public function set_reply(Request $request){
        $data=Util::postMore([
            'id',
            'content'
        ],$request);
        if(!$data['id']) return JsonService::fail('参数错误');
        if(!$data['content']) return JsonService::fail('回复内容不能为空');
        $save['merchant_reply_content']=$data['content'];
        $save['merchant_reply_time']=time();
        $save['rep_id']=$data['id'];
        $save['merchant_name']=($this->getActiveAdminInfo())['real_name'];
        if(!BusinessCourseCommentReply::set($save)) return JsonService::fail('回复失败');
        BusinessCourseReply::edit(['is_reply'=>1],$data['id']);
        return JsonService::successful('回复成功');
    }
    /**
     * 删除评论
     *
     * @return \think\Response
     */
    public function delete($id){
        if(!$id) return JsonService::fail('参数错误');
        if(!BusinessCourseReply::delCategory($id))
            return Json::fail(BusinessCourseReply::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}