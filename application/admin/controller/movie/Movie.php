<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/12
 * Time: 1:57 PM
 */
namespace app\admin\controller\movie;
use app\admin\controller\AuthController;
use app\admin\model\movie\MovieContent;
use app\admin\model\movie\StoreProduct;
use app\admin\model\zhishi\CourseCatalog;
use app\admin\model\movie\MovieCategory as MovieCModel;
use app\admin\model\movie\Movie as MovieModel;
use Monolog\Processor\UidProcessor;
use service\FormBuilder as Form;
use service\getID3;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use service\JsonService;
use think\Url;
class Movie extends AuthController{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(){
        $this->assign('cate',MovieCModel::getTierList());
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
    public function movie_list(){
        $where = Util::getMore([
            ['is_show',''],
            ['cid',$this->request->param('cid','')],
            ['title',''],
            ['page',1],
            ['limit',20],
            ['sort',''],
            ['type','']
        ]);
        return JsonService::successlayui(MovieModel::CourseList($where));
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
        if(MovieModel::where(['id'=>$id])->update([$field=>$value]))
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
        $res=MovieModel::where(['id'=>$id])->update(['is_show'=>(int)$is_show]);
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
            Form::select('cid','视频分类')->setOptions(function(){
                $list = MovieCModel::getTierList();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['title']];
                }
                return $menus;
            })->filterable(1)->required('请选择视频分类'),
            Form::input('title','视频名称')->required('视频名称不能为空'),
            Form::frameImageOne('image_input','视频封面375*210',Url::build('admin/widget.images/index',array('fodder'=>'image_input')))->icon('image')
            ->required('请上传视频封面'),
            Form::radio('type','视频类型',1)->options([['label'=>'横屏','value'=>1],['label'=>'竖屏','value'=>2]]),

            Form::select('goods_id','商品id')->setOptions(function(){
                $list = StoreProduct::where('is_show',1)->where('is_del',0)->select()->toArray();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['store_name']];
                }
                return $menus;
            })->filterable(1)->required('商品不能为空'),

            Form::input('author','作者姓名')->placeholder('请输入讲师姓名')->required('作者姓名不能为空'),
            Form::input('sort','排序')->required('排序不能为空'),
            Form::textarea('synopsis','视频简介')->rows(4)->required('视频简介不能为空'),
            Form::uploadFileOne('content','视频',Url::build('admin/widget.files/upload2'))->col('file')->accept('video/mp4')->format(['mp4'])->maxSize('14417295')->required('视频不能为空'),
            Form::radio('is_show','状态',1)->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])->col(8)
        ];
        $form = Form::make_post_form('添加视频',$field,Url::build('save'),2);
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
        $c=MovieModel::get($id);
        $content = MovieContent::where('nid',$id)->value('content');
        if(!$c || !$content) return Json::fail('数据不存在!');

        $field = [
            Form::select('cid','视频分类',(String)$c->getData('cid'))->setOptions(function(){
                $list = MovieCModel::getTierList();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['title']];
                }
                return $menus;
            })->filterable(1)->required('请选择视频分类'),
            Form::input('title','视频名称',$c->getData('title'))->required('视频名称不能为空'),
            Form::input('sort','视频排序',$c->getData('sort'))->required('视频排序不能为空'),
            Form::frameImageOne('image_input','视频封面375*210',Url::build('admin/widget.images/index',array('fodder'=>'image_input')),$c->getData('image_input'))->icon('image')
                ->required('请上传视频封面'),
            Form::select('goods_id','商品',(String)$c->getData('goods_id'))->setOptions(function(){
                $list = StoreProduct::where('is_show',1)->where('is_del',0)->select()->toArray();
                foreach ($list as $menu){
                    $menus[] = ['value'=>$menu['id'],'label'=>$menu['store_name']];
                }
                return $menus;
            })->filterable(1)->required('请选择商品'),
            Form::uploadFileOne('content','视频',Url::build('admin/widget.files/upload2'))->col('file')->accept('video/mp4')->format(['mp4'])->value($content)->maxSize('51200'),
            Form::input('author','作者姓名',$c->getData('author'))->placeholder('请输入作者姓名')->required('作者姓名不能为空'),
            Form::radio('type','视频类型',$c->getData('type'))->options([['label'=>'横屏','value'=>1],['label'=>'竖屏','value'=>2]]),
            Form::radio('is_show','状态',$c->getData('is_show'))->options([['label'=>'显示','value'=>1],['label'=>'隐藏','value'=>0]])->col(8),
            Form::textarea('synopsis','视频简介',$c->getData('synopsis'))->rows(4)->required('视频简介不能为空'),

        ];
        $form = Form::make_post_form('修改视频',$field,Url::build('update',array('id'=>$id)),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
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
            'image_input',
            'title',
            'type',
            'author',
            'synopsis',
            'is_show',
            'goods_id',
            'type',
            'content'
        ],$request);
        if($data['cid'] == '') return Json::fail('请选择视频分类');
        if(!$data['title']) return Json::fail('请输入视频名称');
        if(!$data['image_input']) return Json::fail('请上传视频封面');
        if(!$data['content']) return Json::fail('请上传视频');

        $data['admin_id'] = $this->adminInfo['id'];
        $data['add_time'] = time();
        $data['status']=0;
        $content = $data['content'];
        unset($data['content']);
        vendor('getid3.getid3');
        $mp3 = new \getID3();
        $info =  $mp3->analyze(ROOT_PATH.DS.$content);
        $data['length']=$info["playtime_string"];
        $movie = MovieModel::set($data);
        $data1 = array(
            'nid' => $movie->id,
            'content' => $content
        );
        MovieContent::set($data1);
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
            'image_input',
            'title',
            'type',
            'author',
            'synopsis',
            'is_show',
            'goods_id',
            'type',
            'content'
        ],$request);
        if($data['cid'] == '') return Json::fail('请选择视频分类');
        if(!$data['title']) return Json::fail('请输入视频名称');
        if(!$data['image_input']) return Json::fail('请上传视频封面');
        if(!$data['content']) return Json::fail('请上传视频');

        $content = $data['content'];
        unset($data['content']);
        vendor('getid3.getid3');
        $mp3 = new \getID3();
        $info =  $mp3->analyze(ROOT_PATH.DS.$content);
        $data['length']=$info["playtime_string"];
        MovieModel::edit($data,$id);
        $data1 = array(
            'content' => $content
        );
        MovieContent::where('nid',$id)->update($data1);
        return Json::successful('编辑视频成功!');
    }
    /**
     * 删除数据
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function delete($id){
        if(!MovieModel::delCategory($id))
            return Json::fail(MovieModel::getErrorInfo('删除失败,请稍候再试!'));
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