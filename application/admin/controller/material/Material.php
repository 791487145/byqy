<?php

namespace app\admin\controller\material;

use app\admin\controller\AuthController;
use service\JsonService;
use service\UtilService as Util;
use service\PHPTreeService as Phptree;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\material\MaterialCategory as MaterialCategoryModel;
use app\admin\model\material\Material as MaterialModel;
use app\admin\model\system\SystemAttachment;

use think\Url;
use service\FormBuilder as Form;
/**
 * 图文管理
 * Class WechatNews
 * @package app\admin\controller\wechat
 */
class Material extends AuthController
{
    /**
     * 显示后台管理员添加的图文
     * @return mixed
     */
    public function index($cid = 0)
    {
        $where = Util::getMore([
            ['title',''],
            ['cid','']
        ],$this->request);
        $this->assign('where',$where);
        if(empty($where['cid']) && $cid){
            $where['cid'] = $cid;
        }
        $catlist = MaterialCategoryModel::where('is_del',0)->select()->toArray();
        //获取分类列表
        if($catlist){
            $tree = $catlist;
            $this->assign(compact('tree'));
        }else{
            $tree = [];
            $this->assign(compact('tree'));
        }

        $this->assign('cate',MaterialCategoryModel::getTierList());
        $this->assign(MaterialModel::getAll($where));
        return $this->fetch();
    }

    public function view_upload(){
        $res = Upload::file('file','material/image');
        if(!$res->status) return Json::fail($res->error,['error' => 1]);
        return Json::successful('上传成功!',['url'=>$res->filePath,'error' => 0]);
    }

    /**
     * 展示页面   添加和删除
     * @return mixed
     */
    public function create(){

        $id = input('id');
        $cid = input('cid');
        $news = array();
        $news['id'] = '';
        $images = '';
        $news['title'] = '';
        $news['author'] = '';
        $news['is_hot'] = '';
        $news['content'] = '';
        $news['cid'] = array();
        if($id){
            $news = MaterialModel::where('n.id',$id)->alias('n')->field('n.*,c.content')->join('MaterialContent c','c.nid=n.id')->find();
            if(!$news) return $this->failedNotice('无此数据');
            $news['cid'] = explode(',',$news['cid']);
            $images = explode(',',$news['image_input']);
        }

        $all = array();
        $select =  0;
        if(!$cid)
            $cid = '';
        else {
            if($id){
                $all = MaterialCategoryModel::where('id',$cid)->where('hidden','neq',0)->column('id,title');
                $select = 1;
            }else{
                $all = MaterialCategoryModel::where('id',$cid)->column('id,title');
                $select = 1;
            }
        }
        if(empty($all)){
            $select =  0;
            $list = MaterialCategoryModel::getTierList();
            $all = [];
            foreach ($list as $menu){
                $all[$menu['id']] = '|-----'.$menu['title'];
            }
        }

        $request = Request::instance();
        $this->assign('url',$request->domain());
        $this->assign('images',$images);
        $this->assign('news',$news);
        $this->assign('all',$all);
        $this->assign('cid',$cid);
        $this->assign('select',$select);
        return $this->fetch();
    }

    /**
     * 上传图文图片
     * @return \think\response\Json
     */
    public function upload_image(){
        $res = Upload::Image($_POST['file'],'wechat/image/'.date('Ymd'));
        //产品图片上传记录
        $fileInfo = $res->fileInfo->getinfo();
        SystemAttachment::attachmentAdd($res->fileInfo->getSaveName(),$fileInfo['size'],$fileInfo['type'],$res->dir,'',5);
        if(!$res->status) return Json::fail($res->error);
        return Json::successful('上传成功!',['url'=>$res->filePath]);
    }

    /**
     * 添加和修改图文
     * @param Request $request
     * @return \think\response\Json
     */
    public function add_new(Request $request){
        $post  = $request->post();

        $data = Util::postMore([
            ['id',0],
            ['cid',[]],
            'title',
            'image_input',
            'content',
            ['sort',0],
            ['is_hot',0],
            ['status',1],],$request);
        $data['cid'] = implode(',',$data['cid']);
        $content = $data['content'];

        unset($data['content']);
        if($data['id']){
            $id = $data['id'];
            unset($data['id']);
            MaterialModel::beginTrans();
            $res1 = MaterialModel::edit($data,$id,'id');
            $res2 = MaterialModel::setContent($id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
//            dump($res);
//            exit();
            MaterialModel::checkTrans($res);
            if($res)
                return Json::successful('修改图文成功!',$id);
            else
                return Json::fail('修改图文失败!',$id);
        }else{
            //dump($content);exit;
            $data['add_time'] = time();
            $data['admin_id'] = $this->adminId;
            $data['author'] = $this->adminInfo['real_name'];
            MaterialModel::beginTrans();
            $res1 = MaterialModel::set($data);
            $res2 = false;
            if($res1)
                $res2 = MaterialModel::setContent($res1->id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
            MaterialModel::checkTrans($res);
            if($res)
                return Json::successful('添加图文成功!',$res1->id);
            else
                return Json::successful('添加图文失败!',$res1->id);
        }
    }

    /**
     * 删除图文
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $res = MaterialModel::del($id);
        if(!$res)
            return Json::fail('删除失败,请稍候再试!');
        else
            return Json::successful('删除成功!');
    }
}