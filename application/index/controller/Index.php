<?php
namespace app\index\controller;

use service\JsonService;
use think\config\driver\Json;
use think\Request;
use xavier\swoole\Component\HttpClient;
use xavier\swoole\WebSocketFrame;

class Index
{
    public function _empty()
    {
//        header('Location:http://www.crmeb.net/');
        exit;
    }
    public function index(Request $request){

        //dump($request->param('id'));
        $data['content']=$request->param('content');
        //$client->pushToClient($data);
        $client=WebSocketFrame::getInstance();

        return \json($client->getArgs());
    }
    public function chart(){
        return view();
    }
}


