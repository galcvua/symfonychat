<?php

declare(strict_types=1);

namespace App\Tests\ApiResource;

use App\Tests\Factory\ChatMessageFactory;
use App\Tests\Simulator\MercurePublisherSimulator;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Mercure\Update;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class ChatMessageResourceTest extends ApiResourceTestCase
{
    use ResetDatabase;
    use Factories;

    protected static array $googlePayloads = [
        'idTokenJohnDoe' => [
            'sub' => '1234567890',
            'name' => 'John Doe',
            'picture' => 'http://example.com/johndoe.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email' => 'johndoe@example.com',
        ],
        'idTokenJaneDoe' => [
            'sub' => '0987654321',
            'name' => 'Jane Doe',
            'picture' => 'http://example.com/janedoe.jpg',
            'given_name' => 'Jane',
            'family_name' => 'Doe',
            'email' => 'janedoe@example.com',
        ],
    ];

    public function testUnauthorizedClentCannotAccessMessages(): void
    {
        ChatMessageFactory::createMany(10);

        static::createClient()->request(method: 'GET', url: '/api/messages');

        self::assertResponseStatusCodeSame(401);
    }

    public function testNotExpiredMessagesAreReturned(): void
    {
        ChatMessageFactory::createMany(10);

        static::createClientWithCredentials('idTokenJohnDoe')->request(method: 'GET', url: '/api/messages');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 10]);
    }

    public function testExpiredMessagesAreNotReturned(): void
    {
        ChatMessageFactory::createMany(10);

        $apiClient = static::createClientWithCredentials('idTokenJohnDoe');

        $container = self::getContainer();

        $messageLifeTime = $container->getParameter('messageLifeTime');
        assert(is_int($messageLifeTime));

        $clock = $container->get('clock');
        assert($clock instanceof ClockInterface);

        $clock->sleep($messageLifeTime + 1);

        $apiClient->request(method: 'GET', url: '/api/messages');

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 0]);
    }

    public function testUnauthorizedUserCannotCreateMessage(): void
    {
        static::createClient()->request(method: 'POST', url: '/api/messages', options: [
            'json' => [
                'message' => 'Hello, World!',
            ],
        ]);

        self::assertResponseStatusCodeSame(401);
    }

    #[Env('MOCK_CLOCK_INITIAL_TIME', '2021-01-01 00:00:00')]
    public function testAuthorizedClientCanCreateMessage(): void
    {
        $client = static::createClientWithCredentials('idTokenJohnDoe');

        $simulator = self::getContainer()->get('App\Tests\Simulator\MercurePublisherSimulator');

        assert($simulator instanceof MercurePublisherSimulator);

        $simulator->setPublisher(function (Update $update): string {
            $message = json_decode($update->getData(), true);
            self::assertEquals('Hello, World!', $message['message']);
            self::assertEquals('2021-01-01T00:00:00+00:00', $message['createdAt']);
            self::assertEquals('John Doe', $message['user']['displayName']);
            self::assertEquals('http://example.com/johndoe.jpg', $message['user']['picture']);

            return '';
        });

        $client->request(method: 'POST', url: '/api/messages', options: [
            'json' => [
                'message' => 'Hello, World!',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['message' => 'Hello, World!']);
        self::assertJsonContains(['createdAt' => '2021-01-01T00:00:00+00:00']);
        self::assertJsonContains(['user' => [
            'displayName' => 'John Doe',
            'picture' => 'http://example.com/johndoe.jpg',
        ]]);
    }

    public function testOnlyCreatorCanAccessGoogleSub(): void
    {
        static::createClientWithCredentials('idTokenJohnDoe')->request(method: 'POST', url: '/api/messages', options: [
            'json' => [
                'message' => 'Hello, World!',
            ],
        ]);

        self::assertResponseIsSuccessful();

        static::createClientWithCredentials('idTokenJohnDoe')->request(method: 'GET', url: '/api/messages');

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['member' => [0 => ['user' => ['googleSub' => '1234567890']]]]);

        $response = static::createClientWithCredentials('idTokenJaneDoe')->request(method: 'GET', url: '/api/messages');

        self::assertResponseIsSuccessful();

        $content = $response->toArray();
        self::assertArrayHasKey('member', $content);
        self::assertCount(1, $content['member']);

        $chatMessage = $content['member'][0];

        self::assertEquals('John Doe', $chatMessage['user']['displayName']);
        self::assertEquals('Hello, World!', $chatMessage['message']);

        self::assertArrayNotHasKey('googleSub', $chatMessage['user']);
    }
}
