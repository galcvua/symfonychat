<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ChatMessage;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * @implements ProcessorInterface<ChatMessage, ChatMessage|void>
 */
#[AsDecorator('api_platform.doctrine.orm.state.persist_processor')]
class SetMessageUserProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<ChatMessage, ChatMessage|void> $innerProcessor
     */
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $user = $this->security->getUser();

        if ($data instanceof ChatMessage && $data->getUser() === null && $user instanceof User) {
            $data->setUser($user);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
