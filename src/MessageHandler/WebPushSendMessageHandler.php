<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\WebPushSendMessage;
use InvalidArgumentException;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Message handler for sending Firebase push notifications.
 */
#[AsMessageHandler]
final class WebPushSendMessageHandler
{
    public function __construct(
        private Messaging $messaging,
    ) {
    }

    public function __invoke(WebPushSendMessage $message): void
    {
        assert($message->token === null || strlen($message->token) > 0);
        assert($message->topic === null || strlen($message->topic) > 0);
        assert(strlen($message->title) > 0);

        $firebaseMessage = match (true) {
            $message->token !== null => CloudMessage::withTarget('token', $message->token),
            $message->topic !== null => CloudMessage::withTarget('topic', $message->topic),
            default => throw new InvalidArgumentException('Token or topic must be set'),
        };

        $notification = Notification::create(title: $message->title, body: $message->body);

        if (!empty($message->data)) {
            $firebaseMessage = $firebaseMessage->withData($message->data);
        }

        $firebaseMessage = $firebaseMessage->withNotification($notification);

        $webPushNotificationArray = ['title' => $message->title];

        if ($message->ttl !== null) {
            $webPushNotificationArray['ttl'] = $message->ttl;
        }

        if (!empty($message->body)) {
            $webPushNotificationArray['body'] = $message->body;
        }

        if (!empty($message->icon)) {
            $webPushNotificationArray['icon'] = $message->icon;
        }

        if (!empty($message->badge)) {
            $webPushNotificationArray['badge'] = $message->badge;
        }

        $webPushConfigArray = ['notification' => $webPushNotificationArray];

        if (!empty($message->link)) {
            $webPushConfigArray['fcm_options'] = ['link' => $message->link];
        }

        $config = WebPushConfig::fromArray($webPushConfigArray);

        $firebaseMessage = $firebaseMessage->withWebPushConfig($config);

        $this->messaging->send($firebaseMessage);
    }
}
