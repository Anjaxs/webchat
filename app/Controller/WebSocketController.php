<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Count;
use App\Model\User;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    const USER_PREFIX = 'uid_';

    const ROOM_PREFIX = 'room';

    /** 房间列表先写死，简化逻辑 */
    const ROOM_LIST = [1, 2];

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
        if (!empty($data['id']) && $user = User::find($data['id'])) {
            $socket->join(SELF::USER_PREFIX . $data['id']);
            $counts = Count::query()->where('user_id', $user->id)->pluck('count', 'room_id');
            $rooms = [];
            foreach (self::ROOM_LIST as $roomId) {
                $rooms[self::ROOM_PREFIX . $roomId] = $counts[$roomId] ?? 0;
            }
            $socket->emit('count', $rooms);
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }
}
