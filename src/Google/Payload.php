<?php

declare(strict_types=1);

namespace App\Google;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class Payload
{
    private function __construct(
        #[Assert\NotBlank]
        public ?string $sub,

        #[Assert\NotBlank]
        public ?string $name,

        #[Assert\NotBlank]
        #[Assert\Url(requireTld: true)]
        public ?string $picture,

        #[Assert\NotBlank]
        public ?string $givenName,

        public ?string $familyName,

        #[Assert\NotBlank]
        #[Assert\Email]
        public ?string $email,
    ) {
    }

    /**
     * @param array<string, string> $jwtPayload
     */
    public static function fromArray(array $jwtPayload): self
    {
        return new self(
            sub: $jwtPayload['sub'] ?? null,
            name: $jwtPayload['name'] ?? null,
            picture: $jwtPayload['picture'] ?? null,
            givenName: $jwtPayload['given_name'] ?? null,
            familyName: $jwtPayload['family_name'] ?? null,
            email: $jwtPayload['email'] ?? null,
        );
    }
}
