<?php

declare (strict_types=1);
namespace App\Model;

/**
 */
class Room extends Model
{
    /**
     * 房间标识前缀
     */
    CONST ROOM_PREFIX = 'room';
 
    /**
     * 房间列表id先写死，简化逻辑
     */
    CONST ID_LIST = [1, 2];
}