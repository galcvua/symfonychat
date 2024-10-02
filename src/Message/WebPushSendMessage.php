<?php

namespace App\Message;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Command for sending a web push notification.
 */
final readonly class WebPushSendMessage
{
    /**
     * @param array<non-empty-string, mixed> $data
     */
    public function __construct(
        #[NotBlank]
        public string $title,

        #[NotBlank(allowNull: true)]
        public ?string $body = null,

        #[NotBlank(allowNull: true)]
        public ?string $icon = null,

        #[NotBlank(allowNull: true)]
        public ?string $badge = null,

        #[NotBlank(allowNull: true)]
        public ?string $link = null,

        public array $data = [],

        public ?int $ttl = null,

        #[NotBlank(allowNull: true)]
        public ?string $topic = null,

        #[NotBlank(allowNull: true)]
        public ?string $token = null,
    ) {
    }

    #[IsTrue(message: 'Either token or topic must be set')]
    public function isTargetSetOnce(): bool
    {
        return count(array_filter([$this->token, $this->topic])) === 1;
    }
}
