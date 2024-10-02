<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Message\WebPushSubscribeMessage;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<WebPushSubscribeMessage, WebPushSubscribeMessage|void>
 */
class WebPushSubscribeProcessor implements ProcessorInterface
{
    /**
     * @param array<string, mixed>|null $firebaseConfig
     */
    public function __construct(
        private Security $security,
        private MessageBusInterface $bus,
        #[Autowire(param: 'firebaseConfig')]
        private ?array $firebaseConfig,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (empty($this->firebaseConfig)) {
            throw new RuntimeException('Firebase config is not set');
        }

        $user = $this->security->getUser();

        assert($user instanceof User);

        $userId = $user->getId()?->toRfc4122();

        assert(!empty($userId));

        $data->setUserId($userId);

        $this->bus->dispatch($data);
    }
}
