<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 9:50 AM
 */
namespace app\admin\controller\business;
use app\admin\controller\AuthController;
use app\admin\model\business\BusinessCourseCategory as CourseCModel;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
use app\admin\model\system\SystemAttachment;


class  BusinessCourseCategory extends AuthController{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $this->assign('pid',$this->request->get('pid',0));
        $this->assign('cate',CourseCModel::getTierList());
        return $this->fetch();
    }
    /*
     *  异步获取分类列表
     *  @return json
     */
    public function category_list(){
        $where = Util::getMore([
            ['is_show',''],
            ['pid',$this->request->param('pid','')],
            ['cate_name',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(CourseCModel::CategoryList($where));
    }
    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_category($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(CourseCModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }
    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        $field = [
            Form::select('pid','父级')->setOptions(function(){
                $list = CourseCModel::getTierList();
                $menus = [['value'=>0,'label'=>'顶级菜单']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name','分类名称'),
            Form::number('sort','排序'),
            Form::radio('is_show','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])
        ];
        $form = Form::make_post_form('添加分类',$field,Url::build('save'),2);
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
        $c = CourseCModel::get($id);
        if(!$c) return Json::fail('数据不存在!');

        $field = [
            Form::select('pid','父级',(string)$c->getData('pid'))->setOptions(function() use($id){
                $list = CourseCModel::getTierList(CourseCModel::where('id','<>',$id));
//                $list = (Util::sortListTier(CategoryModel::where('id','<>',$id)->select()->toArray(),'顶级','pid','cate_name'));
                $menus = [['value'=>0,'label'=>'顶级菜单']];
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name']];
                }
                return $menus;
            })->filterable(1),
            Form::input('cate_name','分类名称',$c->getData('cate_name')),
            Form::number('sort','排序',$c->getData('sort')),
            Form::radio('is_show','状态',$c->getData('is_show'))->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])
        ];

        $form = Form::make_post_form('编辑分类',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_show($is_show='',$id=''){
        ($is_show=='' || $id=='') && JsonService::fail('缺少参数');
        $res=CourseCModel::where(['id'=>$id])->update(['is_show'=>(int)$is_show]);
        if($res){
            return JsonService::successful($is_show==1 ? '显示成功':'隐藏成功');
        }else{
            return JsonService::fail($is_show==1 ? '显示失败':'隐藏失败');
        }
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
            'pid',
            'cate_name',
            'sort',
            ['is_show',0]
        ],$request);
        if($data['pid'] == '') return Json::fail('请选择父类');
        if(!$data['cate_name']) return Json::fail('请输入分类名称');
        if($data['sort'] <0 ) $data['sort'] = 0;
        $data['add_time'] = time();
        CourseCModel::set($data);
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
            'pid',
            'cate_name',
            'sort',
            ['is_show',0]
        ],$request);
        if($data['pid'] == '') return Json::fail('请选择父类');
        if(!$data['cate_name']) return Json::fail('请输入分类名称');
        if($data['sort'] <0 ) $data['sort'] = 0;
        CourseCModel::edit($data,$id);
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

        if(!CourseCModel::delCategory($id))
            return Json::fail(CourseCModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}