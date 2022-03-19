<?php

declare(strict_types=1);

namespace App\Controller;

use AlibabaCloud\Credentials\Credential\Config;
use AlibabaCloud\SDK\Chatbot\V20171011\Chatbot;
use AlibabaCloud\SDK\Chatbot\V20171011\Models\ChatRequest;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\User;
use App\Request\AuthRequest;
use App\Support\Auth;
use App\Support\Str;
use App\Middleware\AuthMiddleware;
use App\Model\Count;
use App\Model\Message;
use App\Model\Room;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @Controller(prefix="api/message")
 */
class MessageController extends AbstractController
{
    /**
     * 获取某一个房间的历史消息
     * @GetMapping(path="history")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function history()
    {
        $roomId = intval(ltrim($this->request->input('roomid'), Room::ROOM_PREFIX));
        $current = intval($this->request->input('msgid', 0));
        if ($roomId <= 0 || $current < 0) {
            throw new BusinessException(ErrorCode::INVALID_PARAM, '无效的房间和页面信息');
        }
        // 分页查询消息
        $messageData = Message::query()
            ->with('user:id,name,avatar')
            ->where('room_id', $roomId)
            ->when($current, function ($query) use ($current) {
                $query->where('id', '<', $current);
            })
            ->take(20)
            ->orderBy('id', 'desc')
            ->get();
        
        return $this->response->json([
            'list' => $messageData,
            'success' => true,
        ]);
    }

    /**
     * 获取所有房间的最新历史消息
     * @GetMapping(path="all_history")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function allHistory()
    {
        
    }
}
