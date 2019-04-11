<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 9:50 AM
 */
namespace app\admin\controller\store;
use app\admin\controller\AuthController;
use app\admin\model\store\StoreProductTag as StoreProductTagCModel;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
use app\admin\model\system\SystemAttachment;


class  StoreProductTag extends AuthController{
    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index(){
        $this->assign('cate',StoreProductTagCModel::getTierList());
        return $this->fetch();
    }
    /*
     *  异步获取分类列表
     *  @return json
     */
    public function category_list(){
        $where = Util::getMore([
            ['is_show',''],
            ['tag_name',''],
            ['page',1],
            ['limit',20],

        ]);
        return JsonService::successlayui(StoreProductTagCModel::CategoryList($where));
    }
    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_category($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(StoreProductTagCModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }

    /**
     * 设置单个产品
     *
     * @return json
     */
    public function set_show($is_show='',$id=''){
        ($is_show=='' || $id=='') && JsonService::fail('缺少参数');
        $res=StoreProductTagCModel::where(['id'=>$id])->update(['is_show'=>(int)$is_show]);
        if($res){
            return JsonService::successful($is_show==1 ? '显示成功':'隐藏成功');
        }else{
            return JsonService::fail($is_show==1 ? '显示失败':'隐藏失败');
        }
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::input('tag_name','标签名称'),
            Form::number('sort','排序'),
            Form::radio('is_show','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])
        ];
        $form = Form::make_post_form('添加标签',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        $c = StoreProductTagCModel::get($id);
        if(!$c) return Json::fail('数据不存在!');

        $field = [
            Form::input('tag_name','标签名称',$c->getData('tag_name')),
            Form::number('sort','排序',$c->getData('sort')),
            Form::radio('is_show','状态',$c->getData('is_show'))->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])
        ];

        $form = Form::make_post_form('编辑标签',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'tag_name',
            'sort',
            ['is_show',0]
        ],$request);
        if(!$data['tag_name']) return Json::fail('请输入分类名称');
        if($data['sort'] <0 ) $data['sort'] = 0;
        $data['add_time'] = time();
        StoreProductTagCModel::set($data);
        return Json::successful('添加分类成功!');
    }
    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'tag_name',
            'sort',
            ['is_show',0]
        ],$request);
        if(!$data['tag_name']) return Json::fail('请输入分类名称');
        if($data['sort'] <0 ) $data['sort'] = 0;
        StoreProductTagCModel::edit($data,$id);
        return Json::successful('修改成功!');
    }
    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(!StoreProductTagCModel::delCategory($id))
            return Json::fail(StoreProductTagCModel::getErrorInfo('删除失败,请先删除分类下商品!'));
        else
            return Json::successful('删除成功!');
    }

}