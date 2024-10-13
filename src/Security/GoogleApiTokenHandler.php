<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class GoogleApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private Client $client,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $apiToken): UserBadge
    {
        $payload = $this->client->verifyIdToken($apiToken);

        if (!$payload) {
            throw new BadCredentialsException();
        }        

        return new UserBadge($payload['sub'], function (string $userIdentifier) use ($payload): User {
            $user = $this->userRepository->findOneBy(['googleSub' => $userIdentifier]);

            if (is_null($user)) {
                $user = new User();
                $user->setGoogleSub($userIdentifier);
            }

            $user
                ->setGoogleName($payload['name'])
                ->setGooglePicture($payload['picture'])
                ->setGoogleGivenName($payload['given_name'])
                ->setGoogleFamilyName($payload['family_name'])
                ->setEmail($payload['email'])
            ;

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;
        });
    }
}
