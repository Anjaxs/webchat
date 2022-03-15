<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Parser\Engine;
use Hyperf\SocketIOServer\Parser\Packet;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;

/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    const USER_PREFIX = 'uid_';

    /**
     * @Event("connect")
     * @param string $data
     */
    public function onConnect(Socket $socket, $data)
    {
        // 应答
        echo 'Event Received: connect' . $data;
        $socket->emit('connect', '欢迎访问聊天室');
    }

    /**
     * @Event("disconnect")
     * @param string $data
     */
    public function onDisconnect(Socket $socket, $data)
    {
        $socket->emit('disconnect', '拜拜~ 欢迎再次光临');
    }

    /**
     * @Event("login")
     * @param string $data
     */
    public function onLogin(Socket $socket, $data)
    {
        if (!empty($data['id']) && User::find($data['id'])) {
            $socket->join(SELF::USER_PREFIX . $data['id']);
            $socket->emit('login', '登录成功');
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }
}
