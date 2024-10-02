<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\ConfigResource;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProviderInterface<ConfigResource>
 */
class ConfigResourceStateProvider implements ProviderInterface
{
    /**
     * @param array<string, string>|null $clientFirebaseConfig
     */
    public function __construct(
        #[Autowire(param: 'messageLifeTime')]
        private int $messageLifeTime,
        #[Autowire(param: 'clientFirebaseConfig')]
        private ?array $clientFirebaseConfig = null,
        #[Autowire(param: 'vapidKey')]
        private ?string $vapidKey = null,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ConfigResource
    {
        return new ConfigResource(
            messageLifeTime: $this->messageLifeTime,
            firebaseConfig: $this->clientFirebaseConfig,
            vapidKey: $this->vapidKey ?: null,
        );
    }
}
