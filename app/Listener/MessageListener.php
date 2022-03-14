<?php
namespace App\Listener;

use App\Event\MessageReceived;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * @Listener
 */
class MessageListener implements ListenerInterface
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function listen(): array
    {
        // 返回一个该监听器要监听的事件数组，可以同时监听多个事件
        return [
            MessageReceived::class,
        ];
    }

    /**
     * @param MessageReceived $event
     */
    public function process(object $event)
    {
        $message = $event->getData();
        $this->logger->info(__CLASS__ . ': 开始处理', $message->toArray());
        if ($message && $message->user_id && $message->room_id && ($message->msg || $message->img)) {
            $message->save();
            $this->logger->info(__CLASS__ . ': 处理完毕');
        } else {
            $this->logger->info(__CLASS__ . ': 消息字段缺失，无法保存');
        }
    }
}
