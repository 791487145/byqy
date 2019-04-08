<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 1:57 PM
 */
namespace app\admin\controller\zhishi;
use app\admin\controller\AuthController;
use app\admin\model\zhishi\CourseCatalog;
use app\admin\model\zhishi\CourseCategory as CourseCModel;
use app\admin\model\zhishi\CourseTag as courseTModel;
use app\admin\model\zhishi\Course as CourseModel;
use Monolog\Processor\UidProcessor;
use service\FormBuilder as Form;
use service\getID3;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
class Course extends AuthController{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $this->assign('cate',CourseCModel::getTierList());
        return $this->fetch();
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function show_catalog($id){
        $this->assign('course_id',$id);
        return $this->fetch();
    }
    /*
   *  异步获取分类列表
   *  @return json
   */
    public function course_list(){
        $where = Util::getMore([
            ['is_show',''],
            ['cid',$this->request->param('cid','')],
            ['course_name',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(CourseModel::CourseList($where));
    }
    /*
  *  异步获取章节列表
  *  @return json
  */
    public function catalog_list(){
        if(!$this->request->param('cid')) return Json::fail('id 为空');
        $where = Util::getMore([

            ['cid',$this->request->param('cid','')],
            ['catalog_title',''],
            ['page',1],
            ['limit',20],
            ['order','']
        ]);
        return JsonService::successlayui(CourseCatalog::CatalogList($where));
    }
    /**
     * 快速编辑
     *
     * @return json
     */
    public function set_category($field='',$id='',$value=''){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(CourseModel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }
    /**
     * 设置单个产品上架|下架
     *
     * @return json
     */
    public function set_show($is_show='',$id=''){
        ($is_show=='' || $id=='') && JsonService::fail('缺少参数');
        $res=CourseModel::where(['id'=>$id])->update(['is_show'=>(int)$is_show]);
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
            Form::select('cid','课程分类')->setOptions(function(){
                $list = CourseCModel::getTierList();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name']];
                }
                return $menus;
            })->filterable(1)->required('请选择课程分类'),
            Form::input('course_name','课程名称')->required('课程名称不能为空'),
            Form::frameImageOne('pic','课程封面375*210',Url::build('admin/widget.images/index',array('fodder'=>'pic')))->icon('image')
            ->required('请上传课程封面'),
            Form::radio('type','课程类型',0)->options([['label'=>'图文','value'=>0],['label'=>'音频','value'=>1],['label'=>'视频','value'=>2]]),
            Form::input('author_name','讲师姓名')->placeholder('请输入讲师姓名')->required('讲师姓名不能为空'),
            Form::uploadImageOne('file','讲师头像',Url::build('admin/widget.images/uploadimg'))->accept('image/jpeg,image/png')->format(['jpeg','jpg','png'])
            ->required('请上传讲师头像'),
            Form::radio('is_cost','收费',1)->options([['label'=>'免费','value'=>1],['label'=>'收费','value'=>2]]),
            Form::input('price','课程价格')->placeholder('请输入课程价格,仅在收费类型下适用'),
            Form::checkbox('tag_id','课程标签')->setOptions(function (){
                $list = courseTModel::all();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['tag_name']];
                }
                return $menus;
            }),
            Form::textarea('description','课程简介')->rows(4),
            Form::radio('is_best','精品推荐',0)->options([['label'=>'否','value'=>0],['label'=>'是','value'=>1]])->col(8),
            Form::radio('is_hot','热门推荐',0)->options([['label'=>'否','value'=>0],['label'=>'是','value'=>1]])->col(8),
            Form::radio('is_show','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])->col(8)
        ];
        $form = Form::make_post_form('添加课程',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 显示子章节创建资源表单页.
     *
     * @return \think\Response
     */
    public function catalog_create($id){
        $c=CourseModel::get($id);
        if(!$c) return Json::fail('数据不存在!');
        $apend=[];
        if($c->getData('type')==0) {//如果是图文到图文添加页面
            $this->assign('course_name',$c->getData('course_name'));
            $this->assign('course_id',$c->getData('id'));
            $this->assign('type',$c->getData('type'));
            return $this->fetch();
        }elseif($c->getData('type')==1){//如果是音频
            $apend=[
                Form::uploadFileOne('content','音频',Url::build('admin/widget.files/upload'))->col('file')->accept('audio/mpeg')->format(['mp3'])->maxSize('20480')->required('请上传音频')
            ];
        }elseif ($c->getData('type')==2){//如果是视频
            $apend=[
                Form::uploadImageOne('file','章节封面200*116',Url::build('admin/widget.images/uploadimg'))->accept('image/jpeg,image/png')->format(['jpeg','jpg','png'])
                    ->required('请上传章节封面'),
                Form::uploadFileOne('content','视频',Url::build('admin/widget.files/upload1'))->col('file')->accept('video/mp4')->format(['mp4'])->maxSize('51200')
            ];
        }

        $field=[
            Form::input('','课程名称',$c->getData('course_name'))->disabled(1),
            Form::hidden('cid',$c->getData('id')),
            Form::hidden('type',$c->getData('type')),
            Form::input('catalog_title','章节标题')->placeholder('请输入章节标题')->required('章节标题不能为空'),
            Form::radio('is_free','试听',0)->options([['label'=>'否','value'=>0],['label'=>'是','value'=>1]]),
            Form::textarea('description','章节简介')->rows(4),

        ];
        $field=array_merge($field,$apend);
        $form = Form::make_post_form('添加课程子章节',$field,Url::build('add_catalog'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id){
        $c=CourseModel::get($id);
        $tag_ids=[];
        if(!$c) return Json::fail('数据不存在!');
        foreach ($c->tags as $item){
            $tag_ids[]=$item->pivot->tag_id;

        }
        if($c->getData('type')==0) $type='图文';
        elseif ($c->getData('type')==1)$type='音频';
        else  $type='视频';
        $field = [
            Form::select('cid','课程分类',(String)$c->getData('cid'))->setOptions(function(){
                $list = CourseCModel::getTierList();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['html'].$menu['cate_name']];
                }
                return $menus;
            })->filterable(1)->required('请选择课程分类'),
            Form::input('course_name','课程名称',$c->getData('course_name'))->required('课程名称不能为空'),
            Form::frameImageOne('pic','课程封面375*210',Url::build('admin/widget.images/index',array('fodder'=>'pic')),$c->getData('pic'))->icon('image')
                ->required('请上传课程封面'),
            Form::input('','课程类型',$type)->disabled(1),
            Form::input('author_name','讲师姓名',$c->getData('author_name'))->placeholder('请输入讲师姓名')->required('讲师姓名不能为空'),
            Form::uploadImageOne('file','讲师头像',Url::build('admin/widget.images/uploadimg'))->accept('image/jpeg,image/png')->format(['jpeg','jpg','png'])
                ->required('请上传讲师头像')->value($c->getData('author_avator')),
            Form::radio('is_cost','收费',$c->getData('is_cost'))->options([['label'=>'免费','value'=>1],['label'=>'收费','value'=>2]]),
            Form::input('price','课程价格',$c->getData('price'))->placeholder('请输入课程价格,仅在收费类型下适用'),
            Form::checkbox('tag_id','课程标签')->setOptions(function (){
                $list = courseTModel::all();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['tag_name']];
                }
                return $menus;
            })->value($tag_ids),
            Form::textarea('description','课程简介',$c->getData('description'))->rows(4),
            Form::radio('is_best','精品推荐',$c->getData('is_best'))->options([['label'=>'否','value'=>0],['label'=>'是','value'=>1]])->col(8),
            Form::radio('is_hot','热门推荐',$c->getData('is_hot'))->options([['label'=>'否','value'=>0],['label'=>'是','value'=>1]])->col(8),
            Form::radio('is_show','状态',$c->getData('is_show'))->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])->col(8)
        ];
        $form = Form::make_post_form('添加课程',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 显示编辑子章节单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function  edit_catalog($id){
        $c=CourseCatalog::get($id);
        if(!$c) return Json::fail('数据不存在!');
        $apend=[];
        if($c->getData('type')==0) {//如果是图文到图文添加页面
            $this->assign('course_name',CourseModel::where('id',$c->getData('cid'))->value('course_name'));
            $this->assign('catalog',$c);
            return $this->fetch();
        }elseif($c->getData('type')==1){//如果是音频
            $apend=[
                Form::uploadFileOne('content','音频',Url::build('admin/widget.files/upload'))->col('file')->accept('audio/mpeg')->format(['mp3'])->value($c->getData('content'))->maxSize('20480'),
                Form::hidden('old_content',$c->getData('content')),
            ];
        }elseif ($c->getData('type')==2){//如果是视频
            $apend=[
                Form::uploadImageOne('file','章节封面200*116',Url::build('admin/widget.images/uploadimg'))->accept('image/jpeg,image/png')->format(['jpeg','jpg','png'])
                    ->required('请上传章节封面')->value($c->getData('pic')),
                Form::hidden('old_content',$c->getData('content')),
                Form::uploadFileOne('content','视频',Url::build('admin/widget.files/upload1'))->col('file')->accept('video/mp4')->format(['mp4'])->value($c->getData('content'))->maxSize('51200')
            ];
        }
        $field=[
            Form::input('','课程名称',CourseModel::where('id',$c->getData('cid'))->value('course_name'))->disabled(1),
            Form::hidden('type',$c->getData('type')),
            Form::hidden('id',$c->getData('id')),
            Form::input('catalog_title','章节标题',$c->getData('catalog_title'))->placeholder('请输入章节标题')->required('章节标题不能为空'),
            Form::radio('is_free','试听',0)->options([['label'=>'否','value'=>0],['label'=>'是','value'=>1]])->value($c->getData('is_free')),
            Form::textarea('description','章节简介',$c->getData('description'))->rows(4),

        ];
        $field=array_merge($field,$apend);
        $form = Form::make_post_form('编辑课程子章节',$field,Url::build('save_catalog'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }
    /**
     * 保存新建的子章节资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function add_catalog(Request $request){
        $data=Util::postMore([
            'cid',
            'catalog_title',
            'content',
            'is_free',
            ['description',''],
            'file',
            'type',
        ],$request);
        if($data['catalog_title'] == '') return Json::fail('请输入章节标题');
        if(empty($data['content']))return Json::fail('请输入章节内容');
        $data['add_time']=time();
        if($data['type']==1||$data['type']==2){
            vendor('getid3.getid3');
            $mp3 = new \getID3();
            $info =  $mp3->analyze(ROOT_PATH.DS.$data['content']);
            $data['length']=$info["playtime_string"];
        }
        $data['pic']=$data['file']?:'';
        unset($data['file']);
        if(!CourseCatalog::set($data))return Json::fail('添加失败');
        return Json::successful('添加课程章节成功!');
    }
    /**
     * 保存编辑的子章节资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save_catalog(Request $request){
        $data=Util::postMore([
            'id',
            'catalog_title',
            'content',
            'type',
            ['description',''],
            ['old_content',''],
            'file',
            'is_free',
        ],$request);
        if($data['catalog_title'] == '') return Json::fail('请输入章节标题');
        if(empty('content'))return Json::fail('请输入章节内容');
        $data['add_time']=time();
        if($data['type']==1||$data['type']==2){
            vendor('getid3.getid3');
            $mp3 = new \getID3();
            $info =  $mp3->analyze(ROOT_PATH.DS.$data['content']);
            if(isset($info['playtime_string'])){
                $data['length']=$info["playtime_string"];
            }
            if($data['old_content']!=$data['content']){
                $filePath=$data['old_content'];
                @unlink(ROOT_PATH.$filePath);
            }
        }
        $data['pic']=$data['file']?:'';
        unset($data['file']);
        unset($data['old_content']);
        if(!CourseCatalog::edit($data,$data['id']))return Json::fail('编辑失败');
        return Json::successful('编辑课程章节成功!');
    }
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request){
        $data=Util::postMore([
            'cid',
            'pic',
            'course_name',
            'type',
            'is_cost',
            'price',
            'author_name',
            'file',
            'is_best',
            'is_hot',
            'is_show',
            ['tag_id',[],'','tag_id'],
            'description'
        ],$request);
        if($data['cid'] == '') return Json::fail('请选择课程分类');
        if(!$data['course_name']) return Json::fail('请输入课程名称');
        if(!$data['pic']) return Json::fail('请上传课程封面');
        if(!$data['file']) return Json::fail('请上传讲师头像');
        $data['add_time']=time();
        $data['author_avator']=$data['file'];
        unset($data['file']);
        $course=CourseModel::set($data);
        //如果有选择了标签
        if(isset($data['tag_id'])&&is_array($data['tag_id'])&&!empty($data['tag_id'])){
            $course->tags()->save($data['tag_id']);
        }
        return Json::successful('添加课程成功!');
    }
    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function update(Request $request,$id){
        $data=Util::postMore([
            'cid',
            'pic',
            'course_name',
            'is_cost',
            'price',
            'is_best',
            'is_hot',
            'is_show',
            'author_name',
            'file',
            ['tag_id',[],'','tag_id'],
            'description'
        ],$request);
        if($data['cid'] == '') return Json::fail('请选择课程分类');
        if(!$data['course_name']) return Json::fail('请输入课程名称');
        if(!$data['pic']) return Json::fail('请上传课程封面');
        if(!$data['file']) return Json::fail('请上传讲师头像');
        $data['add_time']=time();
        $data['author_avator']=$data['file'];
        unset($data['file']);
        $res=CourseModel::edit($data,$id);
        //如果有选择了标签
        if(isset($data['tag_id'])&&is_array($data['tag_id'])&&!empty($data['tag_id'])){
            if($res){
                $course=CourseModel::get($id);
                $course->tags()->detach();
                $course->tags()->save($data['tag_id']);
            }

        }
        return Json::successful('编辑课程成功!');
    }
    /**
     * 删除数据
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function delete($id){
        $count=CourseCatalog::where('cid',$id)->count();
        if($count) return Json::fail('课程下有子章节，请先删除子章节');
        if(!CourseModel::delCategory($id))
            return Json::fail(CourseModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
    /**
     * 删除子章节数据
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function catalog_delete($id){
        $filePath=CourseCatalog::where('id',$id)->value('content');
        $picPath=CourseCatalog::where('id',$id)->value('pic');
        if(!CourseCatalog::delCategory($id))
            return Json::fail(CourseCatalog::getErrorInfo('删除失败,请稍候再试!'));
        else{
            @unlink(ROOT_PATH.$filePath);
            @unlink(ROOT_PATH.$picPath);
            return Json::successful('删除成功!');
        }

    }
}