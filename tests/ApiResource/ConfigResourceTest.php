<?php

declare(strict_types=1);

namespace App\Tests\ApiResource;

use Zalas\PHPUnit\Globals\Attribute\Env;

class ConfigResourceTest extends ApiResourceTestCase
{
    public function testResponseIfClientConfigIsSet(): void
    {
        static::createClient()->request('GET', '/api/config');

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['@id' => '/api/config']);
        self::assertJsonContains(['messageLifeTime' => 600]);
        self::assertJsonContains(['vapidKey' => '1234567890']);
        self::assertJsonContains(['firebaseConfig' => [
            'apiKey' => '987654321',
            'authDomain' => 'symfonychat-test.firebaseapp.com',
            'projectId' => 'symfonychat-test',
            'storageBucket' => 'symfonychat-test.appspot.com',
            'messagingSenderId' => '223322',
            'appId' => '1:223322:web:t2est3',
        ]]);
    }

    #[Env('CLIENT_FIREBASE_CONFIG', 'null')]
    #[Env('VAPID_KEY', '')]
    public function testResponseIfClientConfigIsNotSet(): void
    {
        static::createClient()->request('GET', '/api/config');

        self::assertResponseIsSuccessful();
        self::assertJsonEquals([
            '@context' => '/api/contexts/config',
            '@id' => '/api/config',
            '@type' => 'config',
            'messageLifeTime' => 600,
        ]);
    }
}
