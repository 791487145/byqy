<?php
/**
 * Created by PhpStorm.
 * User: fengxin
 * Date: 2019/2/20
 * Time: 2:39 PM
 */
namespace app\movie\model\movie;
use basic\ModelBasic;
use think\Request;
use traits\ModelTrait;

class MovieContent extends ModelBasic{
    use ModelTrait;

    public function getContentAttr($value){
        $request = Request::instance();
        $http = $request->domain();
        return $http.$value;
    }
}