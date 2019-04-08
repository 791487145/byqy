<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/18
 * Time: 10:26 AM
 */
namespace app\admin\controller\zhishi;
use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use app\admin\model\zhishi\DoctorSection as sectionModel;
use think\Request;
use service\JsonService;
use think\Url;
class DoctorSection extends AuthController{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){

        return $this->fetch();
    }
    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_category($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(sectionModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }
    /**
     * 添加科室
     *
     * @return \think\Response
     */
    public function create(){
        $field=[
            Form::input('section_name','课程名称')->placeholder('请输入科室名称')->required('科室名称不能为空')
        ];
        $form = Form::make_post_form('添加科室',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /*
     *  异步获取科室列表
     *  @return json
     */
    public function section_list(){
        $where = Util::getMore([
            ['section_name',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(sectionModel::SectionList($where));
    }
    /**
     * 编辑科室
     *
     * @return \think\Response
     */
    public function edit($id){
        $s=sectionModel::get($id);
        if(!$s)return Json::fail('数据不存在!');
        $field=[
            Form::input('section_name','课程名称',$s->getData('section_name'))->placeholder('请输入科室名称')->required('科室名称不能为空')
        ];
        $form = Form::make_post_form('编辑科室',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 保存科室
     *
     * @return \think\Response
     */
    public function save(Request $request){
        $data=Util::postMore([
            'section_name'
        ],$request);
        if(!$data['section_name']) return Json::fail('科室名称不能为空');
        if(!sectionModel::set($data))return Json::fail('科室添加失败');
        return JsonService::successful('科室添加成功');
    }
    /**
     * 更新科室
     *
     * @return \think\Response
     */
    public function update(Request $request,$id){
        $data=Util::postMore([
            'section_name'
        ],$request);
        if(!$data['section_name']) return Json::fail('科室名称不能为空');
        if(!sectionModel::edit($data,$id))return Json::fail('科室编辑失败');
        return JsonService::successful('科室编辑成功');
    }
    /**
     * 删除数据
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function delete($id){
        if(!sectionModel::delCategory($id))
            return Json::fail(sectionModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}