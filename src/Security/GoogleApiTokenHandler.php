<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Google\Payload;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GoogleApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private Client $client,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        #[Autowire(lazy: true)]
        private LockFactory $lockFactory,
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $apiToken): UserBadge
    {
        $jwtPayload = $this->client->verifyIdToken($apiToken);

        if (!$jwtPayload) {
            throw new BadCredentialsException();
        }

        $payload = Payload::fromArray($jwtPayload);

        $violations = $this->validator->validate($payload);

        if (count($violations) > 0) {
            throw new AccessDeniedException('The account could not be used to log in.');
        }

        assert($payload->sub !== null);

        return new UserBadge($payload->sub, function (string $userIdentifier) use ($payload): User {
            $user = $this->userRepository->findOneBy(['googleSub' => $userIdentifier]);

            if ($user
                && $user->getGoogleName() === $payload->name
                && $user->getGooglePicture() === $payload->picture
                && $user->getGoogleGivenName() === $payload->givenName
                && $user->getGoogleFamilyName() === $payload->familyName
                && $user->getEmail() === $payload->email
            ) {
                return $user;
            }

            $lock = $this->lockFactory->createLock(resource: 'update_user_' . $payload->sub);

            $lock->acquire(true);

            try {
                $user = $this->userRepository->findOneBy(['googleSub' => $userIdentifier]);

                if (is_null($user)) {
                    $user = new User();
                    $user->setGoogleSub($userIdentifier);
                }

                assert($payload->name !== null);
                assert($payload->picture !== null);
                assert($payload->givenName !== null);
                assert($payload->email !== null);

                $user
                    ->setGoogleName($payload->name)
                    ->setGooglePicture($payload->picture)
                    ->setGoogleGivenName($payload->givenName)
                    ->setGoogleFamilyName($payload->familyName)
                    ->setEmail($payload->email)
                ;

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            } finally {
                $lock->release();
            }

            return $user;
        });
    }
}
