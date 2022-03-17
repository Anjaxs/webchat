<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Count;
use App\Model\Room;
use App\Model\User;
use App\Support\Cache;

class RoomService
{
    /**
     * 获取用户未读消息的数量
     * @param User  $user    用户
     * @param int   $roomid  指定房间, 如果不传，则获取全部
     */
    public function getRoomUnreadCount($user, $roomId = 0)
    {
        $countBuilder = Count::query()->where('user_id', $user->id);
        if ($roomId) {
            $count = $countBuilder->where('room_id', $roomId)->value('count');
            return [Room::ROOM_PREFIX . $roomId => $count];
        }
        $counts = $countBuilder->pluck('count', 'room_id');
        $rooms = [];
        foreach (Room::ID_LIST as $roomId) {
            $rooms[Room::ROOM_PREFIX . $roomId] = $counts[$roomId] ?? 0;
        }
        return $rooms;
    }

    /**
     * 更新房间在线用户
     * @param mixed $roomId  房间id
     * @param User  $user    用户
     * @param int   $act     1: 进入房间  0：离开房间
     */
    public function updateOnlineUsers($roomId, $user, $act)
    {
        $roomUsersKey = 'online_users:' . $roomId;
        $onelineUsers = Cache::get($roomUsersKey);

        if ($act == 0 && !empty($onelineUsers[$user->id])) {
            unset($onelineUsers[$user->id]);
            Cache::set($roomUsersKey, $onelineUsers);
        }
        if ($act == 1) {
            if ($onelineUsers) {
                $onelineUsers[$user->id] = $user;
            } else {
                $onelineUsers = [$user->id => $user];
            }
            Cache::set($roomUsersKey, $onelineUsers);
        }

        return $onelineUsers;
    }

    /**
     * 离开房间
     */
    public function leaveRoom($socket, $data)
    {
        if (!empty($data['id']) && $user = User::find($data['id'])) {
            if (empty($data['roomid'])) {
                return;
            }
            $roomId = ltrim($data['roomid'], Room::ROOM_PREFIX);
            $roomName = Room::ROOM_PREFIX . $roomId;
            $onelineUsers = $this->updateOnlineUsers($roomId, $user, 0);

            $socket->in($roomName)->emit('roomout', ['roomid' => $roomName, 'onlineUsers' => $onelineUsers]);
            $socket->emmit('roomout', ['roomid' => $roomName, 'onlineUsers' => $onelineUsers]);
            $socket->leave($roomName);
        } else {
            $socket->emit('login', '登录后才能进入聊天室');
        }
    }
}
