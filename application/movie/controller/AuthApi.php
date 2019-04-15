<?php
namespace app\movie\controller;

use Api\Express;
use app\admin\model\system\SystemConfig;
use app\movie\model\movie\Movie;
use app\movie\model\movie\MovieCategory;
use app\movie\model\movie\MovieLog;
use app\movie\model\movie\MoviePraiseLog;
use app\movie\model\movie\MovieReply;
use app\movie\model\movie\MovieReplyPraiseLog;
use behavior\routine\RoutineBehavior;
use Knp\Snappy\Image;
use service\JsonService;
use service\GroupDataService;
use service\RoutineBizDataCrypt;
use service\UtilService;
use think\Request;
use think\Db;
use app\routine\model\store\StoreProductAttrValue;

/**
 * 小程序接口
 * Class AuthApi
 * @package app\routine\controller
 *
 */
class AuthApi extends AuthController{

    /**
     * 首页banner,cate
     */
    public function index()
    {
        $data['banner'] = GroupDataService::getData('byqy_home_banner')?:[];//banner图
        $data['movie_cate'] = MovieCategory::where('is_show',1)->field('id,title,is_show')->select()->toArray();//菜单
        $data['status'] = 1;
        return JsonService::successful($data);
    }

    /**
     * 列表
     * @param Request $request
     */
    public function movie_list(Request $request){
        $data = UtilService::postMore([
            ['title',''],
            ['cid',''],
            ['page',0],
            ['limit',20]
        ],$request);
        $list = Movie::getList('id,cid,title,image_input,goods_id,length,type',$data);
        return JsonService::successful($list);
    }

    /**
     * 视频详情
     * @param Request $request
     * @throws \think\Exception
     */
    public function movie_detail(Request $request)
    {
        $id = $request->post('id','');
        if(!$id) return JsonService::fail('参数不全');
        if(!Movie::validWhere()->where('id',$id)->count()) return JsonService::fail('当前视频不存在');
        $list = Movie::getMovie($id,$this->userInfo['uid'],'id,title,goods_id,type,replay_num,share_num,collect_num,like_num,synopsis');
        return JsonService::successful($list);
    }

    /**
     * 评论列表
     * @param Request $request
     * @throws \think\Exception
     */
    public function movie_reply_list(Request $request)
    {
        $data = UtilService::postMore([
            ['mv_id',''],
            ['page',0],
            ['limit',20]
        ],$request);
        if(!$data['mv_id']) return JsonService::fail('参数不全');
        if(!Movie::validWhere()->where('id',$data['mv_id'])->count()) return JsonService::fail('当前视频不存在');
        $list = MovieReply::reply_list($data,$this->userInfo['uid']);
        return JsonService::successful($list);
    }

    /**
     * 评论
     * @param Request $request
     */
    public function movie_reply_pro(Request $request)
    {
        $data = UtilService::postMore([
            ['mv_id',''],
            ['comment',''],
        ],$request);
        if(!$data['mv_id'] || !$data['comment']) return JsonService::fail('参数不全');
        Movie::where('id',$data['mv_id'])->setInc('replay_num');
        $data1 = array(
            'comment' => $data['comment'],
            'uid' => $this->userInfo['uid'],
            'add_time' => time(),
            'mv_id' => $data['mv_id']
        );
        MovieReply::set($data1);
        return JsonService::successful('添加成功');
    }

    /**
     * 评论点赞
     * @param Request $request
     */
    public function movie_reply_praise(Request $request)
    {
        $data = UtilService::postMore([
            ['reply_id',''],

        ],$request);
        if(!$data['reply_id']) return JsonService::fail('参数不全');
        $param = array(
            'user_id' => $this->userInfo['uid'],
            'reply_id' => $data['reply_id']
        );
        if(MoviePraiseLog::where($param)->count()){
            Db::startTrans();
            MovieReply::where('id',$data['reply_id'])->setDec('praise_num');
            MoviePraiseLog::where($param)->delete();

            return JsonService::successful('取消成功');
        }else{
            Db::startTrans();
            MovieReply::where('id',$data['reply_id'])->setInc('praise_num');
            MoviePraiseLog::set($param);
            Db::commit();
            return JsonService::successful('点赞成功');
        }
    }

    /**
     * 收藏
     * @param Request $request
     * @throws \think\Exception
     */
    public function movie_collect(Request $request)
    {
        $data = UtilService::postMore([
            ['mv_id',''],
        ],$request);
        if(!$data['mv_id']) return JsonService::fail('参数不全');

        $param = array(
            'user_id' => $this->userInfo['uid'],
            'movie_id' => $data['mv_id'],
            'action' => 1
        );
        if(MovieLog::where($param)->count()){
            Movie::where('id',$data['mv_id'])->setDec('collect_num');
            MovieLog::where($param)->delete();
        }else{
            Movie::where('id',$data['mv_id'])->setInc('collect_num');
            MovieLog::set($param);
        }
        return JsonService::successful('操作成功');
    }

    /**
     * 喜欢
     * @param Request $request
     * @throws \think\Exception
     */
    public function movie_like(Request $request)
    {
        $data = UtilService::postMore([
            ['mv_id',''],
        ],$request);
        if(!$data['mv_id']) return JsonService::fail('参数不全');

        $param = array(
            'user_id' => $this->userInfo['uid'],
            'movie_id' => $data['mv_id'],
            'action' => 2
        );
        if(MovieLog::where($param)->count()){
            Movie::where('id',$data['mv_id'])->setDec('like_num');
            MovieLog::where($param)->delete();
        }else{
            Movie::where('id',$data['mv_id'])->setInc('like_num');
            MovieLog::set($param);
        }
        return JsonService::successful('操作成功');
    }

    /**
     * 分享
     * @param Request $request
     * @throws \think\Exception
     */
    public function movie_share(Request $request)
    {
        $data = UtilService::postMore([
            ['mv_id',''],
        ],$request);
        Movie::where('id',$data['mv_id'])->setInc('share_num');

        return JsonService::successful('操作成功');
    }
    /**
     * 生成视频海报图片
     */
    public function createPoster(){
        $data=UtilService::postMore([
            ['id',0],
        ],$this->request);
        $movieinfo=Movie::where('id',$data['id'])->field('id,title,image_input')->find();
        if(!$movieinfo||!$data['id'])return JsonService::fail('生成图片失败,视频不存在');
        $file=ROOT_PATH.DS.UPLOAD_PATH.'/poster/movie-poster-'.$movieinfo->id.'.png';
        try{
            $snappy=new Image(ROOT_PATH.'vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64');
            $this->assign([
                'title'=>$movieinfo->title,
                'pic'=>$movieinfo->image_input,
            ]);
            $html=$this->fetch('admin@poster/index');
//            return $html;
            $snappy->setOption('width',375);
            $snappy->setOption('height',650);
            $snappy->setOption('format','png');
            if (file_exists($file)) {
                @unlink($file);
            }
            $snappy->generateFromHtml($html, $file);
        }catch (\Exception $e){
            return JsonService::fail('生成图片失败'.$e->getMessage());
        }
        return JsonService::successful('生成图片成功',SystemConfig::getValue('site_url').DS.UPLOAD_PATH.'/poster/movie-poster-'.$movieinfo->id.'.png');
    }
}
