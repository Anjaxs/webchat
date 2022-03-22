<?php

declare(strict_types=1);

namespace App\Controller;

use AlibabaCloud\Credentials\Credential\Config;
use AlibabaCloud\SDK\Chatbot\V20171011\Chatbot;
use AlibabaCloud\SDK\Chatbot\V20171011\Models\ChatRequest;
use App\Model\User;
use App\Request\AuthRequest;
use App\Support\Auth;
use App\Support\Str;
use App\Middleware\AuthMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @Controller(prefix="api/chat")
 */
class ChatController extends AbstractController
{
    /**
     * @PostMapping(path="robot")
     * @Middlewares({
     *     @Middleware(AuthMiddleware::class)
     * })
     */
    public function robot()
    {
        $chatbot = new Chatbot(new Config([
            'accessKeyId'     => config('ali_accesskey_id'),
            'accessKeySecret' => config('ali_accesskey_secret'),
            'endpoint'        => 'chatbot.cn-shanghai.aliyuncs.com',
        ]));
        $user = Auth::user();
        $data = [
            'instanceId' => config('ali_chatbot_id'),
            'senderNick' => $user->name,
            'senderId'   => $user->id,
            'utterance'  => $this->request->input('question'),
        ];
        if ($sessionId = $this->request->input('session_id')) {
            $data['sessionId'] = $sessionId;
        }
        $result = $chatbot->chat(new ChatRequest($data));
        return $this->response->json(['result' => $result->body, 'success' => true]);
    }
}
