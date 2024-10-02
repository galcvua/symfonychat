<?php

namespace App\MessageHandler;

use App\Message\WebPushAbstractSubscribtionMessage;
use Kreait\Firebase\Contract\Messaging;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class WebPushSubscribeMessageHandler
{
    public function __construct(
        private Messaging $messaging,
    ) {
    }

    public function __invoke(WebPushAbstractSubscribtionMessage $message): void
    {
        $token = $message->getToken();

        dump($this->messaging->subscribeToTopics(registrationTokenOrTokens: $token, topics: [
            WebPushAbstractSubscribtionMessage::ALL_MESSAGES_TOPIC,
            $message->getUserId(),
        ]));
    }
}
