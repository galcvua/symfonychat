<?php

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\ChatMessage;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ChatMessageFilterExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private ClockInterface $clock,
        #[Autowire(param: 'messageLifeTime')]
        private int $messageLifeTime,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($resourceClass !== ChatMessage::class) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(sprintf('%s.hidden=false', $rootAlias))
            ->andWhere(sprintf('%s.createdAt >= :timeLimit', $rootAlias))
            ->setParameter('timeLimit', $this->clock->now()->modify(
                sprintf('-%d seconds', $this->messageLifeTime)
            ))
        ;
    }
}
