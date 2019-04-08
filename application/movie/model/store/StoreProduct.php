<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\movie\model\store;

use service\PHPExcelService;
use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\admin\model\order\StoreOrder;
use app\admin\model\system\SystemConfig;

/**
 * 产品管理 model
 * Class StoreProduct
 * @package app\admin\model\store
 */
class StoreProduct extends ModelBasic
{
    use ModelTrait;

}