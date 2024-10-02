<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ChatMessage;
use App\Entity\User;
use App\Message\WebPushSendMessage;
use App\Message\WebPushSubscribeMessage;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<ChatMessage, ChatMessage|void>
 */
#[AsDecorator(decorates: 'api_platform.doctrine.orm.state.persist_processor', priority: 10)]
class SendFirebasePushProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<ChatMessage, ChatMessage|void> $innerProcessor
     * @param array<string, mixed>|null                         $firebaseConfig
     */
    public function __construct(
        private ProcessorInterface $innerProcessor,
        private MessageBusInterface $bus,
        private RequestStack $requestStack,
        #[Autowire(param: 'firebaseConfig')]
        private ?array $firebaseConfig,
        #[Autowire(param: 'firebaseBadgeIcon')]
        private string $firebaseBadgeIcon,
        #[Autowire(param: 'messageLifeTime')]
        private int $messageLifeTime,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $return = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        if ($data instanceof ChatMessage && !empty($this->firebaseConfig)) {
            $user = $data->getUser();

            assert($user instanceof User);

            $chatMessageExpiresAt = $data->getCreatedAt()?->modify(
                sprintf('+%d seconds', $this->messageLifeTime)
            );

            $title = $user->getDisplayName();
            $imageUrl = $user->getGooglePicture() ?? '';
            $link = $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost();

            $badge = match (true) {
                empty($this->firebaseBadgeIcon) => null,
                strpos($this->firebaseBadgeIcon, '://') !== false => $this->firebaseBadgeIcon,
                default => $this->requestStack->getCurrentRequest()?->getSchemeAndHttpHost() . $this->firebaseBadgeIcon,
            };

            if (strlen($title) > 0 && strlen($imageUrl) > 0 && !empty($link)) {
                $this->bus->dispatch(new WebPushSendMessage(
                    title: $title,
                    body: null,
                    icon: $imageUrl,
                    link: $link,
                    topic: WebPushSubscribeMessage::ALL_MESSAGES_TOPIC,
                    badge: $badge,
                    ttl: $this->messageLifeTime,
                    data: [
                        'chatMessageId' => $data->getId(),
                        'chatMessageCreatedAt' => $data->getCreatedAt()?->format(DateTimeInterface::ATOM),
                        'chatMessageExpiresAt' => $chatMessageExpiresAt?->format(DateTimeInterface::ATOM),
                    ],
                ));
            }
        }

        return $return;
    }
}
