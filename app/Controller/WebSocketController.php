<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Count;
use App\Model\Room;
use App\Model\User;
use App\Support\Cache;
use App\Services\RoomService;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Di\Annotation\Inject;

/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    /**
     * @Inject
     * @var RoomService
     */
    protected $roomService;

    /**
     * @Event("connect")
     */
    public function onConnect(Socket $socket, $data)
    {
        // 应答
        echo 'Event Received: connect' . $data;
        $socket->emit('connect', '欢迎访问聊天室');
    }

    /**
     * @Event("disconnect")
     */
    public function onDisconnect(Socket $socket, $data)
    {
        $this->roomService->leaveRoom($socket, $data);
    }

    /**
     * @Event("login")
     */
    public function onLogin(Socket $socket, $data)
    {
        if (!empty($data['id']) && $user = User::find($data['id'])) {
            $socket->join(User::ROOM_PREFIX . $user->id);
            $socket->emit('count', $this->roomService->getRoomUnreadCount($user));
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }

    /**
     * @Event("room")
     */
    public function onRoom(Socket $socket, $data)
    {
        if (!empty($data['id']) && $user = User::find($data['id'])) {
            // 从请求数据中获取房间ID
            if (empty($data['roomid'])) {
                return;
            }
            $roomId = ltrim($data['roomid'], Room::ROOM_PREFIX);
            // 重置用户与fd关联
            // Redis::command('hset', ['socket_id', $user->id, $socket->getSender()]);
            
            // 更新在线用户信息
            $onelineUsers = $this->roomService->updateOnlineUsers($roomId, $user, 1);

            $roomName = Room::ROOM_PREFIX . $roomId;
            $socket->join($roomName);
            $socket->to($roomName)->emit('room', ['roomid' => $roomName, 'onlineUsers' => $onelineUsers]);  // 给房间其他人发
            $socket->emit('room', ['roomid' => $roomName, 'onlineUsers' => $onelineUsers]);  // 给自己发
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }

    /**
     * @Event("roomout")
     */
    public function onRoomout(Socket $socket, $data)
    {
        $this->roomService->leaveRoom($socket, $data);
    }

    /**
     * @Event("read_messages")
     */
    public function onReadMessages(Socket $socket, $data)
    {
        if (!empty($data['id']) && $user = User::find($data['id'])) {
            if (empty($data['roomid'])) {
                return;
            }
            $roomId = ltrim($data['roomid'], Room::ROOM_PREFIX);
            // 将该房间下用户未读消息清零
            Count::query()->updateOrCreate(['user_id' => $user->id, 'room_id' => $roomId], ['count' => 0]);
            $socket->emit('count', $this->roomService->getRoomUnreadCount($user, $roomId));
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }
}
