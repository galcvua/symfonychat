<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\ConfigResourceStateProvider;

#[ApiResource(
    description: 'Confiuration to provide to the client app',
    shortName: 'config',
    operations: [
        new Get(
            uriTemplate: '/config',
            provider: ConfigResourceStateProvider::class,
        ),
    ],
)]
readonly class ConfigResource
{
    /**
     * @param array<string, string>|null $firebaseConfig
     */
    public function __construct(
        public int $messageLifeTime,
        #[ApiProperty(
            property: 'firebaseConfig',
            openapiContext: [
                'oneOf' => [['type' => 'object'], ['type' => 'null']],
                'properties' => [
                    'apiKey' => ['type' => 'string'],
                    'authDomain' => ['type' => 'string'],
                    'projectId' => ['type' => 'string'],
                    'storageBucket' => ['type' => 'string'],
                    'messagingSenderId' => ['type' => 'string'],
                    'appId' => ['type' => 'string'],
                    'measurementId' => ['oneOf' => [['type' => 'string'], ['type' => 'null']]],
                ],
            ]
        )]
        public ?array $firebaseConfig,
        public ?string $vapidKey,
    ) {
    }
}
