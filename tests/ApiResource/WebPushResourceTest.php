<?php

declare(strict_types=1);

namespace App\Tests\ApiResource;

use App\Message\WebPushSubscribeMessage;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zenstruck\Foundry\Test\ResetDatabase;

#[Env('GOOGLE_APPLICATION_CREDENTIALS', self::FAKED_GOOGLE_SERVICE_CREDETIALS)]
class WebPushResourceTest extends ApiResourceTestCase
{
    use ResetDatabase;

    protected static array $googlePayloads = [
        'idTokenJohnDoe' => [
            'sub' => '1234567890',
            'name' => 'John Doe',
            'picture' => 'http://example.com/johndoe.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email' => 'johndoe@example.com',
        ],
    ];

    public function testSubscriptionRequestAccepted(): void
    {
        $client = static::createClientWithCredentials('idTokenJohnDoe');

        $client->request(method: 'POST', url: '/api/webpush/subscribe', options: [
            'json' => ['token' => 'firebaseToken'],
        ]);

        self::assertResponseStatusCodeSame(202);

        $transport = self::getContainer()->get('messenger.transport.webpush');

        self::assertInstanceOf(TransportInterface::class, $transport);

        $queue = $transport->get();
        self::assertCount(1, $queue);

        $message = iterator_to_array($queue)[0]->getMessage();
        self::assertInstanceOf(WebPushSubscribeMessage::class, $message);
        self::assertSame('firebaseToken', $message->getToken());
    }

    public function testViolationIfTokenIsNotProvided(): void
    {
        static::createClientWithCredentials('idTokenJohnDoe')
            ->request(method: 'POST', url: '/api/webpush/subscribe');

        self::assertResponseStatusCodeSame(400);
    }

    public function testViolationIfTokenIsEmpty(): void
    {
        static::createClientWithCredentials('idTokenJohnDoe')
            ->request(method: 'POST', url: '/api/webpush/subscribe', options: [
                'json' => ['token' => ''],
            ]);

        self::assertResponseStatusCodeSame(422);
    }
}
