<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Message\WebPushSendMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(WebPushSendMessage::class)]
class SendWebPushMessageTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
        ;
    }

    public function testValidMessage(): void
    {
        $message = new WebPushSendMessage(
            title: 'Test title',
            body: 'Test body',
            icon: 'https://example.com/icon.png',
            link: 'https://example.com',
            topic: 'all_messages',
            badge: 'https://example.com/badge.png',
        );

        $violations = $this->validator->validate($message);

        self::assertCount(0, $violations);
    }

    public function testMessageWithoutTitle(): void
    {
        $message = new WebPushSendMessage(
            title: '',
            body: 'Test body',
            icon: 'https://example.com/icon.png',
            link: 'https://example.com',
            topic: 'all_messages',
            badge: 'https://example.com/badge.png',
        );

        $violations = $this->validator->validate($message);

        self::assertCount(1, $violations);
        self::assertSame('This value should not be blank.', $violations[0]?->getMessage());
        self::assertSame('title', $violations[0]->getPropertyPath());
    }

    public function testMessageWithoutTarget(): void
    {
        $message = new WebPushSendMessage(
            title: 'Test title',
            body: 'Test body',
            icon: 'https://example.com/icon.png',
            link: 'https://example.com',
            badge: 'https://example.com/badge.png',
        );

        $violations = $this->validator->validate($message);

        self::assertCount(1, $violations);
        self::assertSame('Either token or topic must be set', $violations[0]?->getMessage());
    }

    public function testMessageWithExtraTarget(): void
    {
        $message = new WebPushSendMessage(
            title: 'Test title',
            body: 'Test body',
            icon: 'https://example.com/icon.png',
            link: 'https://example.com',
            topic: 'all_messages',
            token: 'token',
            badge: 'https://example.com/badge.png',
        );

        $violations = $this->validator->validate($message);

        self::assertCount(1, $violations);
        self::assertSame('Either token or topic must be set', $violations[0]?->getMessage());
    }
}
