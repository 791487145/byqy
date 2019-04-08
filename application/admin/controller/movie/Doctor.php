<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/15
 * Time: 9:25 AM
 */
namespace app\admin\controller\zhishi;
use app\admin\controller\AuthController;
use app\admin\model\zhishi\DoctorSection as sectionModel;
use app\admin\model\zhishi\Doctor as doctorModel;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
class Doctor extends AuthController{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $this->assign('cate',sectionModel::all());
        return $this->fetch();
    }
    /*
      *  异步获取医师列表
      *  @return json
      */
    public function doctor_list(){
        $where = Util::getMore([
            ['sid',$this->request->param('sid','')],
            ['doctor_name',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(doctorModel::DoctorList($where));
    }
    /**
     * 添加医师
     *
     * @return \think\Response
     */
    public function create(){
        $field=[
            Form::input('doctor_name','医师姓名')->placeholder('请输入医师姓名')->required('医师姓名不能为空'),
            Form::uploadImageOne('file','医师头像',Url::build('admin/widget.images/uploadimg',array('type'=>2)))->accept('image/jpeg,image/png')->format(['jpeg','jpg','png'])
                ->required('请上传医师头像'),
            Form::select('hospital_level','医院等级')->setOptions([
                ['value'=>'一甲','label'=>'一级甲等'],
                ['value'=>'二甲','label'=>'二级甲等'],
                ['value'=>'三甲','label'=>'三级甲等'],
                ['value'=>'一乙','label'=>'一级乙等'],
                ['value'=>'二乙','label'=>'二级乙等'],
                ['value'=>'三乙','label'=>'三级乙等'],
                ['value'=>'一丙','label'=>'一级丙等'],
                ['value'=>'二丙','label'=>'二级丙等'],
                ['value'=>'三丙','label'=>'三级丙等'],
            ])->required('请选择医院等级'),
            Form::input('hospital','所属医院')->placeholder('请输入所属医院')->required('所属医院不能为空'),
            Form::select('sid','科室')->setOptions(function (){
                $list = sectionModel::all();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['section_name']];
                }
                return $menus;
            })->filterable(1)->required('请选择科室'),
            Form::input('job','职位')->placeholder('请输入职位')->required('职位不能为空'),
            Form::number('working_year','从业年数')->required('请输入从业年'),
            Form::textarea('be_good','医师擅长')->placeholder('请输入医师擅长，多个用，分割')->rows(3),
            Form::textarea('resume','医师简介')->rows(4),
        ];
        $form = Form::make_post_form('添加医师',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 编辑医师
     *
     * @return \think\Response
     */
    public function edit($id){
        $d=doctorModel::get($id);
        if(!$d)return Json::fail('数据不存在');
        $field=[
            Form::input('doctor_name','医师姓名',$d->getData('doctor_name'))->placeholder('请输入医师姓名')->required('医师姓名不能为空'),
            Form::uploadImageOne('file','医师头像',Url::build('admin/widget.images/uploadimg',array('type'=>2)))->accept('image/jpeg,image/png')->format(['jpeg','jpg','png'])
                ->required('请上传医师头像')->value($d->getData('doctor_avatar')),
            Form::select('hospital_level','医院等级',(String)$d->getData('hospital_level'))->setOptions([
                ['value'=>'一甲','label'=>'一级甲等'],
                ['value'=>'二甲','label'=>'二级甲等'],
                ['value'=>'三甲','label'=>'三级甲等'],
                ['value'=>'一乙','label'=>'一级乙等'],
                ['value'=>'二乙','label'=>'二级乙等'],
                ['value'=>'三乙','label'=>'三级乙等'],
                ['value'=>'一丙','label'=>'一级丙等'],
                ['value'=>'二丙','label'=>'二级丙等'],
                ['value'=>'三丙','label'=>'三级丙等'],
            ])->required('请选择医院等级'),
            Form::input('hospital','所属医院',$d->getData('hospital'))->placeholder('请输入所属医院')->required('所属医院不能为空'),
            Form::select('sid','科室',(String)$d->getData('sid'))->setOptions(function (){
                $list = sectionModel::all();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['section_name']];
                }
                return $menus;
            })->filterable(1)->required('请选择科室'),
            Form::input('job','职位',$d->getData('job'))->placeholder('请输入职位')->required('职位不能为空'),
            Form::number('working_year','从业年数',$d->getData('working_year'))->required('请输入从业年数'),
            Form::textarea('be_good','医师擅长',$d->getData('be_good'))->placeholder('请输入医师擅长，多个用，分割')->rows(3),
            Form::textarea('resume','医师简介',$d->getData('resume'))->rows(4),
        ];
        $form = Form::make_post_form('添加医师',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 保存医师
     *
     * @return \think\Response
     */
    public function save(Request $request){
        $data=Util::postMore([
            'doctor_name',
            'sid',
            'file',
            'hospital',
            'working_year',
            'be_good',
            'hospital_level',
            'job',
            'resume'
        ],$request);
        if(!$data['doctor_name'])return Json::fail('医师姓名不能为空');
        if(!$data['file'])return Json::fail('请上传医师头像');
        if(!$data['hospital'])return Json::fail('所属医院不能为空');
        if(!$data['job'])return Json::fail('职位不能空');
        if(!$data['sid'])return Json::fail('请选择科室');
        $data['doctor_avatar']=$data['file'];
        unset($data['file']);
        $data['add_time']=time();
        $data['section_name']=sectionModel::where('id',$data['sid'])->value('section_name');
        if(!doctorModel::set($data)) return Json::fail('添加医师失败');
        return Json::successful('添加医师成功');
    }
    /**
     * 更新医师
     *
     * @return \think\Response
     */
    public function update(Request $request,$id){
        $data=Util::postMore([
            'doctor_name',
            'sid',
            'file',
            'hospital_level',
            'hospital',
            'working_year',
            'be_good',
            'job',
            'resume'
        ],$request);
        if(!$data['doctor_name'])return Json::fail('医师姓名不能为空');
        if(!$data['file'])return Json::fail('请上传医师头像');
        if(!$data['hospital'])return Json::fail('所属医院不能为空');
        if(!$data['job'])return Json::fail('职位不能空');
        if(!$data['sid'])return Json::fail('请选择科室');
        $data['doctor_avatar']=$data['file'];
        unset($data['file']);
        $data['section_name']=sectionModel::where('id',$data['sid'])->value('section_name');
        if(!doctorModel::edit($data,$id)) return Json::fail('编辑医师失败');
        return Json::successful('编辑医师成功');
    }
    /**
     * 删除数据
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function delete($id){
        $filePath=doctorModel::where('id',$id)->value('doctor_avatar');
        if(!doctorModel::delCategory($id))
            return Json::fail(doctorModel::getErrorInfo('删除失败,请稍候再试!'));
        else{
            @unlink(ROOT_PATH.$filePath);
            return Json::successful('删除成功!');
        }

    }
}
