<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/21
 * Time: 10:47 AM
 */
namespace app\zhishi_routine\model\course;
use app\admin\model\system\SystemConfig;
use basic\ModelBasic;
use traits\ModelTrait;

class CourseCatalog extends ModelBasic{
    use ModelTrait;
    public function getAddTimeAttr($value){
        return date('Y-m-d ',$value);
    }
    public function getContentAttr($value){
        if($this->getData('type')==1||$this->getData('type')==2){
            return SystemConfig::getValue('site_url').$value;
        }
        return $value;
    }
    public function getLengthAttr($value){
        return $value?:'';
    }
    public function getPicAttr($value){

        if($value&&!preg_match('/(http:\/\/)|(https:\/\/)/i', $value)){
            return SystemConfig::getValue('site_url').$value;
        }
        return $value;
    }

    /**
     * 上一章
     * @param $catalog_info
     * @return mixed
     */
    public static function  prevCatalog($catalog_info){
        return self::where(['type'=>$catalog_info->getData('type'),'cid'=>$catalog_info->getData('cid')])->where('id','<',$catalog_info->getData('id'))->order('id desc')->value('id');
    }

    /**
     * 下一章
     * @param $catalog_info
     * @return mixed
     */
    public static function  nextCatalog($catalog_info){
       return self::where(['type'=>$catalog_info->getData('type'),'cid'=>$catalog_info->getData('cid')])->where('id','>',$catalog_info->getData('id'))->value('id');
    }
}