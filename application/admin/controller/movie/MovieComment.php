<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/25
 * Time: 9:25 AM
 */
namespace app\admin\controller\movie;
use app\admin\controller\AuthController;
use app\admin\model\movie\MovieReply;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
class MovieComment extends AuthController{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $where=Util::getMore([
            ['mv_id',''],
            ['comment',''],
        ],$this->request);
        $comments=MovieReply::systemPage($where);
        $this->assign('where',$where);
        $this->assign($comments);
        return $this->fetch();
    }

    /**
     * 删除评论
     * @return \think\Response
     */
    public function delete($id){
        if(!$id) return JsonService::fail('参数错误');
        if(!MovieReply::delCategory($id))
            return Json::fail(MovieReply::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}