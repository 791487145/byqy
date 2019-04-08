<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/3/5
 * Time: 1:13 PM
 */
namespace app\admin\controller\reseller;
use app\admin\controller\AuthController;
use app\admin\model\reseller\ResellerLevel;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use think\Request;
use think\Url;

class  ResellerSetting extends AuthController{
    /*
     * 代理列表
     */
    public function index(){
        return $this->fetch();
    }
    public function reseller_list(){
        $resell_list=ResellerLevel::order('resell_level')->select();
        return JsonService::successlayui(0,$resell_list);

    }
    /**
     * 添加代理等级
     */
    public function create(){
        $field=[
            Form::input('resell_name','代理名称')->required('代理名称不能为空'),
            Form::input('reseller_brokerage','代理成本价比例')
                ->placeholder('商品成本价折扣比例0 - 100,例:5 = 统一成本价-统一成本价的5%')->number('请输入正确的数字'),
            Form::select('add_type','层级位置',(String)1)->setOptions([
                ['value'=>1,'label'=>'默认最后'],
                ['value'=>2,'label'=>'于层级开头'],

            ]),

        ];
        $form = Form::make_post_form('添加代理等级',$field,Url::build('save'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 编辑代理等级
     * @param $id
     * @return mixed
     */
    public function edit($id){
        $s=ResellerLevel::get($id);
        if(!$s) return JsonService::fail('数据不存在');
        $field=[
            Form::input('resell_name','代理名称',$s->getData('resell_name'))->required('代理名称不能为空'),
            Form::input('reseller_brokerage','代理成本价比例',$s->getData('reseller_brokerage'))
                ->placeholder('商品成本价折扣比例0 - 100,例:5 = 统一成本价-统一成本价的5%'),


        ];
        $form = Form::make_post_form('编辑代理等级',$field,Url::build('update'),2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 快速编辑
     * @param $field
     * @param $id
     * @param $value
     */
    public function set_reseller($field, $id, $value){
        $field=='' || $id=='' || $value=='' && JsonService::fail('缺少参数');
        if(ResellerLevel::where(['id'=>$id])->update([$field=>$value]))
            return JsonService::successful('保存成功');
        else
            return JsonService::fail('保存失败');
    }
    /**
     * 保存代理
     * @param Request $request
     */
    public function save(Request $request){
        $data=Util::postMore([
            'resell_name',
            ['reseller_brokerage',0],
            ['add_type',1]
        ],$request);
        if(!$data['resell_name']) return JsonService::fail('代理名称不能为空');
        if($data['reseller_brokerage']<0||$data['reseller_brokerage']>100) return JsonService::fail('代理成本价比例最低为0,最大不能超过100');
        if(ResellerLevel::getResellerCount()>=5) return JsonService::fail('代理最多不可超过5级');
        $insert['resell_name']=$data['resell_name'];
        $insert['reseller_brokerage']=$data['reseller_brokerage'];
        $resell_level=1;
        if($data['add_type']==1){//默认最后
            $resell_level=ResellerLevel::order('resell_level desc')->value('resell_level')?(ResellerLevel::order('resell_level desc')->value('resell_level'))+1:1;
        }elseif ($data['add_type']==2){//开头插入
            if(!ResellerLevel::sortLevel()) return JsonService::fail('添加失败');
        }
        $insert['resell_level']=$resell_level;
        if(!ResellerLevel::set($insert)) return JsonService::fail('添加失败');
        return JsonService::successful('添加代理等级成功');
    }
}