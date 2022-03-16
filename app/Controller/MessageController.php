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
use App\Model\Message;
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
     * @GetMapping(path="history")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function history()
    {
        $roomId = intval(ltrim($this->request->input('roomid'), 'room'));
        $current = intval($this->request->input('msgid', 0));
        if ($roomId <= 0 || $current < 0) {
            throw new BusinessException(ErrorCode::INVALID_PARAM, '无效的房间和页面信息');
        }
        // 分页查询消息
        $messageData = Message::query()
            ->where('room_id', $roomId)
            ->where('id', '>', $current)
            ->take(20)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->response->json([
            'list' => $messageData,
            'success' => true,
        ]);
    }
}
