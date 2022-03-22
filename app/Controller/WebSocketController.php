<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Count;
use App\Model\Message;
use App\Model\Room;
use App\Model\User;
use App\Services\RoomService;
use Carbon\Carbon;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    /**
     * @Inject
     * @var Redis
     */
    protected $redis;

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
        if (!empty($data['token']) && $user = User::where('api_token', $data['token'])->first()) {
            // 将用户与指定fd连接关联起来保存到Redis中
            $this->redis->hset('socket_id', (string)$user->id, $socket->getSid());
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
        if (!empty($data['token']) && $user = User::where('api_token', $data['token'])->first()) {
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
        if (!empty($data['token']) && $user = User::where('api_token', $data['token'])->first()) {
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

    /**
     * @Event("message")
     */
    public function onMessage(Socket $socket, $data)
    {
        if (!empty($data['token']) && $user = User::where('api_token', $data['token'])->first()) {
            // 获取消息内容
            $msg = $data['msg'];
            $img = $data['img'];
            $roomId = intval(ltrim($data['room_id'], Room::ROOM_PREFIX));
            $roomName = Room::ROOM_PREFIX . $roomId;
            // 消息内容或房间号不能为空
            if((empty($msg) && empty($img)) || empty($roomId)) {
                return;
            }
            // 将消息保存到数据库
            $message = new Message();
            $message->user_id = $user->id;
            $message->room_id = $roomId;
            $message->msg = $msg;
            $message->img = $img;
            $message->created_at = Carbon::now()->toDateTimeString();
            $message->save();
            $message->load('user:id,name,avatar');
            // 将消息广播给房间内所有用户
            $message->clientId = $data['clientId'];
            $message->status = 'finish';
            $socket->emit('message', $message);
            $socket->to($roomName)->emit('message', $message);
            // 更新所有用户本房间未读消息数
            $userIds = $this->redis->hgetall('socket_id');
            $rooms = [];
            foreach ($userIds as $userId => $socketId) {
                // 更新每个用户未读消息数并将其发送给对应在线用户
                $result = Count::where('user_id', $userId)->where('room_id', $roomId)->first();
                if ($result) {
                    $result->count += 1;
                    $result->save();
                    $rooms[$roomName] = $result->count;
                } else {
                    // 如果某个用户未读消息数记录不存在，则初始化它
                    $count = new Count();
                    $count->user_id = $user->id;
                    $count->room_id = $roomId;
                    $count->count = 1;
                    $count->save();
                    $rooms[$roomName] = 1;
                }
            }
            $socket->to($roomName)->emit('count', $rooms);
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }
}
