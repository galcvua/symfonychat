<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_GOOGLE_SUB', fields: ['googleSub'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: '`user`')]
#[ApiResource(security: 'is_granted("ROLE_USER")', operations: [])]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([ChatMessage::CHAT_MESSAGE_READ])]
    #[ApiProperty(security: 'object == user')]
    private ?string $googleSub = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googlePicture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleGivenName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleFamilyName = null;

    #[ORM\Column]
    #[Timestampable(on: 'create')]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Timestampable(on: 'update')]
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, ChatMessage>
     */
    #[ORM\OneToMany(targetEntity: ChatMessage::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $chatMessages;

    public function __construct()
    {
        $this->chatMessages = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getGoogleSub(): ?string
    {
        return $this->googleSub;
    }

    public function setGoogleSub(string $googleSub): static
    {
        $this->googleSub = $googleSub;

        return $this;
    }

    public function getGooglePicture(): ?string
    {
        return $this->googlePicture;
    }

    public function setGooglePicture(string $googlePicture): static
    {
        $this->googlePicture = $googlePicture;

        return $this;
    }

    public function getGoogleName(): ?string
    {
        return $this->googleName;
    }

    public function setGoogleName(string $googleName): static
    {
        $this->googleName = $googleName;

        return $this;
    }

    public function getGoogleGivenName(): ?string
    {
        return $this->googleGivenName;
    }

    public function setGoogleGivenName(string $googleGivenName): static
    {
        $this->googleGivenName = $googleGivenName;

        return $this;
    }

    public function getGoogleFamilyName(): ?string
    {
        return $this->googleFamilyName;
    }

    public function setGoogleFamilyName(?string $googleFamilyName): static
    {
        $this->googleFamilyName = $googleFamilyName;

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

    #[Groups([ChatMessage::CHAT_MESSAGE_READ])]
    #[ApiProperty(property: 'displayName')]
    public function getDisplayName(): string
    {
        return $this->googleName ?? $this->email ?? '';
    }

    #[Groups([ChatMessage::CHAT_MESSAGE_READ])]
    #[ApiProperty(property: 'picture')]
    public function getPicture(): ?string
    {
        return $this->googlePicture;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): static
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages->add($chatMessage);
            $chatMessage->setUser($this);
        }

        return $this;
    }

    public function removeChatMessage(ChatMessage $chatMessage): static
    {
        if ($this->chatMessages->removeElement($chatMessage)) {
            // set the owning side to null (unless already changed)
            if ($chatMessage->getUser() === $this) {
                $chatMessage->setUser(null);
            }
        }

        return $this;
    }
}
