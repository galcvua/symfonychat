<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ChatMessageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChatMessageRepository::class)]
#[ORM\Index(columns: ['created_at'])]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'message',
    security: 'is_granted("ROLE_USER")',
    mercure: true,
    operations: [
        new GetCollection(
            output: ChatMessage::class,
            description: 'Get the list of chat messages',
            normalizationContext: ['groups' => [self::CHAT_MESSAGE_READ]],
        ),
        new Post(
            input: ChatMessage::class,
            description: 'Create a new chat message',
        ),
    ],
    normalizationContext: ['groups' => [self::CHAT_MESSAGE_READ]],
    denormalizationContext: ['groups' => [self::CHAT_MESSAGE_WRITE]],
)]
class ChatMessage
{
    public const CHAT_MESSAGE_READ = 'chat_message:read';
    public const CHAT_MESSAGE_WRITE = 'chat_message:write';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([self::CHAT_MESSAGE_READ])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'chatMessages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::CHAT_MESSAGE_READ])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups([self::CHAT_MESSAGE_READ])]
    #[Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups([self::CHAT_MESSAGE_READ])]
    #[Timestampable(on: 'update')]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::CHAT_MESSAGE_READ, self::CHAT_MESSAGE_WRITE])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $message = null;

    #[ORM\Column]
    private bool $hidden = false;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }
}
