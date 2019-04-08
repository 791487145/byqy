<?php
/**
 * 参考think-swoole2.0开发
 * author:xavier
 * email:49987958@qq.com
 */

namespace xavier\swoole;

use Swoole\Table;
use think\Error;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use think\Config;
use think\Loader;

/**
 * Swoole Http Server 命令行服务类
 */
class WebServer extends Server
{
    protected $app;
    protected $appPath;
    protected $table;
    protected $monitor;
    protected $cachetable;
    protected $lastMtime;
    protected static $http;
    protected $fieldType = [
        'int'    => Table::TYPE_INT,
        'string' => Table::TYPE_STRING,
        'float'  => Table::TYPE_FLOAT,
    ];

    protected $fieldSize = [
        Table::TYPE_INT    => 4,
        Table::TYPE_STRING => 32,
        Table::TYPE_FLOAT  => 8,
    ];

    /**
     * 架构函数
     * @access public
     */
    public function __construct($host, $port, $mode = SWOOLE_PROCESS, $sockType = SWOOLE_SOCK_TCP)
    {
        parent::__construct($host, $port, $mode , $sockType);
    }

    public function getSwoole()
    {
        return $this->swoole;
    }

    public function setAppPath($path)
    {
        $this->appPath = $path;
    }

    public function setMonitor($interval = 2, $path = [])
    {
        $this->monitor['interval'] = $interval;
        $this->monitor['path']     = (array)$path;
    }

    public function table(array $option)
    {
        $size        = !empty($option['size']) ? $option['size'] : 1024;
        $this->table = new Table($size);

        foreach ($option['column'] as $field => $type) {
            $length = null;

            if (is_array($type)) {
                list($type, $length) = $type;
            }

            if (isset($this->fieldType[$type])) {
                $type = $this->fieldType[$type];
            }

            $this->table->column($field, $type, isset($length) ? $length : $this->fieldSize[$type]);
        }

        $this->table->create();
    }

    public function option(array $option)
    {
        // 设置参数
        if (!empty($option)) {
            $this->swoole->set($option);
        }
        foreach ($this->event as $event) {
            // 自定义回调
            if (!empty($option[$event])) {
                $this->swoole->on($event, $option[$event]);
            } elseif (method_exists($this, 'on' . $event)) {
                $this->swoole->on($event, [$this, 'on' . $event]);
            }
        }
    }

    /**
     * 此事件在Worker进程/Task进程启动时发生,这里创建的对象可以在进程生命周期内使用
     *
     * @param $server
     * @param $worker_id
     */
    public function onWorkerStart($server, $worker_id)
    {
        // 应用实例化
        $this->app = new Application($this->appPath);
        $this->app->setSwoole($this->swoole);
        $this->lastMtime = time();
        \think\Hook::listen('swoole_on_woker_start', $worker_id);
        if ($this->table) {
            $this->app['swoole_table'] = $this->table;
        }
        Loader::addClassMap([
            'think\\cache\\driver\\Table' => __DIR__ . '/cache/driver/Table.php',
        ]);

        $this->initServer($server, $worker_id);

        if (0 == $worker_id && $this->monitor) {
            $this->monitor($server);
        }
        //只在一个进程内执行定时任务
        if (0 == $worker_id) {
            $this->timer($server);
        }
    }

    /**
     * 自定义初始化Swoole
     * @param $server
     * @param $worker_id
     */
    public function initServer($server, $worker_id)
    {
        $wokerStart = Config::get('swoole.wokerstart');
        if ($wokerStart) {
            if (is_string($wokerStart) && class_exists($wokerStart)) {
                $obj = new $wokerStart($server, $worker_id);
                $obj->run();
                unset($obj);
            } elseif ($wokerStart instanceof \Closure) {
                $wokerStart($server, $worker_id);
            }
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * 文件监控
     *
     * @param $server
     */
    protected function monitor($server)
    {
        $paths = $this->monitor['path'] ?: [APP_PATH];
        $timer = $this->monitor['interval'] ?: 2;

        $server->tick($timer, function () use ($paths, $server) {
            foreach ($paths as $path) {
                $dir      = new \RecursiveDirectoryIterator($path);
                $iterator = new \RecursiveIteratorIterator($dir);

                foreach ($iterator as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                        continue;
                    }

                    if ($this->lastMtime < $file->getMTime()) {
                        $this->lastMtime = $file->getMTime();
                        echo '[update]' . $file . " reload...\n";
                        \think\Hook::listen('swoole_reload_file', $file);
                        $server->reload();
                        return;
                    }
                }
            }
        });
    }

    public function timer($server)
    {
        $timer    = Config::get('swoole.timer');
        $interval = intval(Config::get('swoole.interval'));
        if ($timer) {
            $interval = $interval > 0 ? $interval : 1000;
            $systimer = Timer::instance();
            $server->tick($interval, function () use ($systimer, $server) {
                $systimer->run($server);
            });
        }
    }

    /**
     * request回调
     * @param $request
     * @param $response
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        $response->header('Access-Control-Allow-Origin', $request->header['origin'] ?? '');
        $response->header('Access-Control-Allow-Methods', 'OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'x-requested-with,session_id,Content-Type,token,Origin');
        $response->header('Access-Control-Max-Age', '86400');
        $response->header('Access-Control-Allow-Credentials', 'true');
        $request->post['http_server']=$this->getSwoole();
        \think\Hook::listen('swoole_on_request', $request);
        $this->app->swooleHttp($request, $response);

    }

    public function onTask(HttpServer $serv, $task_id, $fromWorkerId, $data)
    {
        if (is_string($data) && class_exists($data)) {
            $taskObj = new $data;
            if (method_exists($taskObj, 'run')) {
                $taskObj->run($serv, $task_id, $fromWorkerId);
                unset($taskObj);
            }
        }

        if (is_object($data) && method_exists($data, 'run')) {
            $data->run($serv, $task_id, $fromWorkerId);
            unset($data);
        }
        \think\Hook::listen('swoole_on_task', $data);
        if ($data instanceof SuperClosure) {
            return $data($serv, $task_id, $data);
        } else {
            $serv->finish($data);
        }

    }

    public function onMessage($server, $frame)
    {
        $this->app->swooleWebSocket($server, $frame);
    }

    public function cachetable()
    {
        $this->cachetable = new CacheTable();
    }

    public function getCacheTable()
    {
        return $this->cachetable;
    }

    public function onFinish(HttpServer $serv, $task_id, $data)
    {
        \think\Hook::listen('swoole_on_finish', $data);
        if ($data instanceof SuperClosure) {
            $data($serv, $task_id, $data);
        }
    }

    protected function exception($response, $e)
    {
        if ($e instanceof \Exception) {
            $handler = Error::getExceptionHandler();
            $handler->report($e);

            $resp    = $handler->render($e);
            $content = $resp->getContent();
            $code    = $resp->getCode();

            $response->status($code);
            $response->end($content);
        } else {
            $response->status(500);
            $response->end($e->getMessage());
        }

        throw $e;
    }

    public function setHttp($http)
    {
        self::$http=$http;
    }

    public static function getHttp()
    {
        return self::$http;
    }
}