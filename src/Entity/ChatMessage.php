<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\ChatMessageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
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
            normalizationContext: ['groups' => ['chat_message:read']],
        ),
        new Post(
            input: ChatMessage::class,
            description: 'Create a new chat message',
        ),
    ],
    normalizationContext: ['groups' => ['chat_message:read']],
    denormalizationContext: ['groups' => ['chat_message:write']],
)]
class ChatMessage
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['chat_message:read'])]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'chatMessages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['chat_message:read'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['chat_message:read'])]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['chat_message:read'])]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['chat_message:read', 'chat_message:write'])]
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

    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function setCreatedAtUpdatedAtValue(): static
    {
        $this->updatedAt = new DateTimeImmutable();

        if ($this->createdAt === null) {
            $this->createdAt = new DateTimeImmutable();
        }

        return $this;
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
