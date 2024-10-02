<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Validator\Constraints as Assert;

abstract class WebPushAbstractSubscribtionMessage
{
    public const ALL_MESSAGES_TOPIC = 'all_messages';

    /**
     * @var non-empty-string
     */
    private string $userId;

    /** @param non-empty-string $token */
    public function __construct(
        #[Assert\NotBlank]
        private string $token,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return non-empty-string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param non-empty-string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }
}
