<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Auth;
use Hyperf\Context\Context;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoole\Constant;

class ResourceMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $currUrl = $request->getUri()->getPath();
        $ext = pathinfo($currUrl, PATHINFO_EXTENSION);
        if (!empty($ext) && $ext !== 'php') {
            if (!config('server.settings.' . Constant::OPTION_ENABLE_STATIC_HANDLER)) {
                return $handler->handle($request);
            }
            $staticFile = config('server.settings.' . Constant::OPTION_DOCUMENT_ROOT) . $currUrl;
            if (!is_file($staticFile)) {
                return Context::get(ResponseInterface::class)
                    ->withHeader('Server', 'crashing')
                    ->withBody(new SwooleFileStream(readlink($staticFile)));
            }
            
        }
        return $handler->handle($request);
    }
}