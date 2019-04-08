<?php

namespace app\movie\controller;

use app\admin\model\system\SystemConfig;
use app\routine\model\user\User;
use app\routine\model\routine\RoutineServer;
use app\routine\model\user\RoutineUser;
use app\routine\model\user\WechatUser;
use service\JsonService;
use service\UtilService;
use think\Controller;
use think\Request;

class Login extends Controller{



    /**
     * 获取用户信息
     * @param Request $request
     * @return \think\response\Json
     */

    public function index(Request $request){
        $data = UtilService::postMore([['info',[]]],$request);//获取前台传的code
        $data = $data['info'];
        $resObj['unionId']='';
        unset($data['info']);
        $res = $this->setCode($data['code']);
        if(!isset($res['openid'])) return JsonService::fail('openid获取失败');
        if(isset($data['encryptedData'])&&isset($data['iv'])){
            if($data['encryptedData']&&$data['iv']){
                $result=$this->resolveMiniUserInfo($res['session_key'],$data['encryptedData'],$data['iv']);
                $resObj=json_decode($result,true);
                if(!isset($resObj['unionId'])){
                    JsonService::fail('登录失败,unionId 为空！');
                }
            }
        }
        if(isset($res['unionid'])) $data['unionid'] = $res['unionid'];
        else $data['unionid'] = $resObj['unionId'];
        $data['routine_openid'] = $res['openid'];
        $data['session_key'] = $res['session_key'];
        $dataOauthInfo = RoutineUser::routineOauth($data);
        $data['uid'] = $dataOauthInfo['uid'];
        $data['page'] = $dataOauthInfo['page'];
        $data['status'] = RoutineUser::isUserStatus($data['uid']);
        return JsonService::successful($data);
    }
    /**
     * 分销员登录获取用户信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function promoter(Request $request){
        list($account,$pwd) = UtilService::postMore(['account','pwd'],$request,true);
        if(!$account || !$pwd) return JsonService::fail('请输入登录账号');
        if(!$pwd) return JsonService::fail('请输入登录密码');
        if(!User::be(['account'=>$account,'is_promoter'=>1])) return JsonService::fail('登陆账号不存在!');
        $userInfo = User::where('account',$account)->find();
        if($userInfo['pwd'] != md5($pwd)){
            return JsonService::fail('账号或密码输入错误!');
        }
        $WechatUser=WechatUser::find($userInfo['uid']);
        return JsonService::successful($WechatUser);

    }
    /**
     * 根据前台传code  获取 openid 和  session_key //会话密匙
     * @param string $code
     * @return array|mixed
     */
    public function setCode($code = ''){
        if($code == '') return [];
        $routineAppId = SystemConfig::getValue('routine_appId');//小程序appID
        $routineAppSecret = SystemConfig::getValue('routine_appsecret');//小程序AppSecret
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$routineAppId.'&secret='.$routineAppSecret.'&js_code='.$code.'&grant_type=authorization_code';
        return json_decode(RoutineServer::curlGet($url),true);
    }

    /**
     * 获取网站logo
     */
    public function get_enter_logo(){
        $siteLogo = SystemConfig::getValue('routine_logo');
        $siteName = SystemConfig::getValue('routine_name');
        $data['site_logo'] = $siteLogo;
        $data['site_name'] = $siteName;
        return JsonService::successful($data);
    }

    /**
     * 获取网站顶部颜色
     */
    public function get_routine_style(){
        $routineStyle = SystemConfig::getValue('routine_style');
        $data['routine_style'] = $routineStyle;
        return JsonService::successful($data);
    }

    /**
     * 获取客服电话
     */
    public function get_site_service_phone(){
        $siteServicePhone = SystemConfig::getValue('site_service_phone');
        $data['site_service_phone'] = $siteServicePhone;
        return JsonService::successful($data);
    }
    /*
     * 用session_key解密小程序userinfo
     */
    public function resolveMiniUserInfo($session_key,$encryptedData,$iv){
        if($session_key==""||$encryptedData==""||$iv=="")return [];
        $routineAppId = SystemConfig::getValue('routine_appId');//小程序appID
        if(strlen($session_key)!=24){
            return JsonService::fail('session_key 长度不够');
        }
        $aesKey=base64_decode($session_key);
        if(strlen($iv)!=24){
            return JsonService::fail('iv 长度不够');
        }
        $aesIV=base64_decode($iv);
        $aesCipher=base64_decode($encryptedData);
        //var_dump('key:'.$aesKey,'   iv:'.$aesIV,'   cipher:'.$aesCipher);
        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataOjb=json_decode($result);
        //var_dump($result);
        if($dataOjb==null){
            return JsonService::fail('解密失败');
        }
        if($dataOjb->watermark->appid!=$routineAppId){
            return JsonService::fail('解密失败,appid不对');
        }
        return $result;
    }
}