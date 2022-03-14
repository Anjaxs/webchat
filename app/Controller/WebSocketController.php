<?php
declare(strict_types=1);

namespace App\Controller;

use App\Event\MessageReceived;
use App\Model\User;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Inject 
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function onMessage($server, Frame $frame): void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
        // $frame->fd 是客户端 id，$frame->data 是客户端发送的数据
        $this->logger->info("从 {$frame->fd} 接收到的数据: {$frame->data}");
        $message = json_decode($frame->data);
        // 基于 Token 的用户认证校验
        if (empty($message->token) || !($user = User::where('api_token', $message->token)->first())) {
            $this->logger->warning("用户" . $message->name . "已经离线，不能发送消息");
            $server->push($frame->fd, "离线用户不能发送消息");  // 告知用户离线状态不能发送消息
        } else {
            // 触发消息接收事件
            $this->eventDispatcher->dispatch(new MessageReceived($user));
            unset($message->token);  // 从消息中去掉当前用户令牌字段
            foreach ($server->connections as $fd) {
                if (!$server->isEstablished($fd)) {
                    // 如果连接不可用则忽略
                    continue;
                }
                $server->push($fd, json_encode($message)); // 服务端通过 push 方法向所有连接的客户端发送数据
            }
        }
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen($server, Request $request): void
    {
        $server->push($request->fd, 'Opened');
    }
}
