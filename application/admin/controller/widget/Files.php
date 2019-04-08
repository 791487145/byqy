<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/13
 * Time: 3:30 PM
 */
namespace app\admin\controller\widget;
use app\admin\controller\AuthController;
use think\Exception;
use think\Request;
use think\Url;
use app\admin\model\system\SystemAttachment as SystemAttachmentModel;
use service\UploadService as Upload;
use service\JsonService as Json;
use service\UtilService as Util;
use service\FormBuilder as Form;
class Files extends AuthController{
    /**
     * 上传文件
     * @return \think\response\Json
     */
    public function upload(Request $request)
    {
        try{
            $pid = input('pid')!= NULL ?input('pid'):session('pid');
            $res = Upload::file('content','zhishi'.DS.'mp3'.DS.date('Y').DS.date('m').DS.date('d'));
            //$thumbPath = Upload::thumb($res->dir);
            //产品图片上传记录
            $fileInfo = $res->fileInfo->getinfo();
            //入口是public需要替换图片路径
            if(strpos(PUBILC_PATH,'public') == false){
                $res->dir = str_replace('public/','',$res->dir);
            }

            //SystemAttachmentModel::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,$res->dir,$pid);
            $info = array(
//            "originalName" => $fileInfo['name'],
                "name" => $res->fileInfo->getSaveName(),
                "url" => '.'.$res->dir,
                "size" => $fileInfo['size'],
                "type" => $fileInfo['type'],
                "state" => "SUCCESS",
                'filePath'  =>$res->dir
            );
            return Json::successful('上传成功',$info);
        }catch (Exception $e){
            return Json::fail('上传失败'.$e->getMessage());
        }

    }
    /**
     * 上传文件
     * @return \think\response\Json
     */
    public function upload1(Request $request)
    {
        try{
            $pid = input('pid')!= NULL ?input('pid'):session('pid');
            $res = Upload::file('content','zhishi'.DS.'video'.DS.date('Y').DS.date('m').DS.date('d'));
            if(!$res->status) return Json::fail('上传失败',$res->error);
            //$thumbPath = Upload::thumb($res->dir);
            //产品图片上传记录
            $fileInfo = $res->fileInfo->getinfo();
            //入口是public需要替换图片路径
            if(strpos(PUBILC_PATH,'public') == false){
                $res->dir = str_replace('public/','',$res->dir);
            }

            //SystemAttachmentModel::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,$res->dir,$pid);
            $info = array(
//            "originalName" => $fileInfo['name'],
                "name" => $res->fileInfo->getSaveName(),
                "url" => '.'.$res->dir,
                "size" => $fileInfo['size'],
                "type" => $fileInfo['type'],
                "state" => "SUCCESS",
                'filePath'  =>$res->dir,
                'res'=>$res,
            );
            return Json::successful('上传成功',$info);
        }catch (Exception $e){
            return Json::fail('上传失败'.$e->getMessage());
        }

    }

    /**
     * 上传文件
     * @return \think\response\Json
     */
    public function upload2(Request $request)
    {
        try{
            $pid = input('pid')!= NULL ?input('pid'):session('pid');
            $res = Upload::file('content','movie'.DS.'video'.DS.date('Y').DS.date('m').DS.date('d'));
            if(!$res->status) return Json::fail('上传失败',$res->error);
            //$thumbPath = Upload::thumb($res->dir);
            //产品图片上传记录
            $fileInfo = $res->fileInfo->getinfo();
            //入口是public需要替换图片路径
            if(strpos(PUBILC_PATH,'public') == false){
                $res->dir = str_replace('public/','',$res->dir);
            }

            //SystemAttachmentModel::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,$res->dir,$pid);
            $info = array(
//            "originalName" => $fileInfo['name'],
                "name" => $res->fileInfo->getSaveName(),
                "url" => '.'.$res->dir,
                "size" => $fileInfo['size'],
                "type" => $fileInfo['type'],
                "state" => "SUCCESS",
                'filePath'  =>$res->dir,
                'res'=>$res,
            );
            return Json::successful('上传成功',$info);
        }catch (Exception $e){
            return Json::fail('上传失败'.$e->getMessage());
        }

    }
}