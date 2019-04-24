<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/24
 * Time: 14:54
 */

namespace GoSwoole\Route\EasyRoute\Aspect;


use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Around;
use GoSwoole\BaseServer\Server\Server;
use GoSwoole\Route\EasyRoute\DispatchRoute;
use Monolog\Logger;

class RouteAspect implements Aspect
{
    /**
     * @var DispatchRoute
     */
    private $dispatchRoute;

    public function __construct(DispatchRoute $dispatchRoute)
    {
        $this->dispatchRoute = $dispatchRoute;
    }

    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(GoSwoole\BaseServer\Server\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundRequest(MethodInvocation $invocation)
    {
        try {
            list($request, $response) = $invocation->getArguments();
            $result = $this->dispatchRoute->handle($request, $response);
            if ($result != null) {
                if (!is_string($result)) {
                    $result = json_encode($result);
                }
                $response->end($result);
            }
            $response->end("");
        }catch (\Throwable $e){
            $log = Server::$instance->getLog();
            $log->error($e);
        }
        return;
    }
}