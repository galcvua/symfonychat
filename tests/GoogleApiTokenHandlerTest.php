<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\GoogleApiTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class GoogleApiTokenHandlerTest extends TestCase
{
    private MockObject&UserRepository $userRepository;
    private MockObject&Client $client;
    private MockObject&EntityManagerInterface $entityManager;
    private GoogleApiTokenHandler $googleApiTokenHandler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->client = $this->createMock(Client::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->googleApiTokenHandler = new GoogleApiTokenHandler(
            $this->userRepository,
            $this->client,
            $this->entityManager
        );
    }

    public function testGetUserBadgeFromValidToken(): void
    {
        $apiToken = 'valid_token';
        $payload = [
            'sub' => '1234567890',
            'name' => 'John Doe',
            'picture' => 'http://example.com/johndoe.jpg',
            'given_name' => 'John',
            'family_name' => 'Doe',
            'email' => 'johndoe@example.com',
        ];

        $this->client->method('verifyIdToken')
            ->with($apiToken)
            ->willReturn($payload);

        $this->userRepository->method('findOneBy')
            ->with(['googleSub' => $payload['sub']])
            ->willReturn(null);

        $userBadge = $this->googleApiTokenHandler->getUserBadgeFrom($apiToken);
        $user = $userBadge->getUser();

        assert($user instanceof User);
        self::assertInstanceOf(UserBadge::class, $userBadge);
        self::assertEquals($payload['sub'], $user->getGoogleSub());
        self::assertEquals($payload['name'], $user->getGoogleName());
        self::assertEquals($payload['picture'], $user->getGooglePicture());
        self::assertEquals($payload['given_name'], $user->getGoogleGivenName());
        self::assertEquals($payload['family_name'], $user->getGoogleFamilyName());
        self::assertEquals($payload['email'], $user->getEmail());
    }

    public function testUpdateExistingUser(): void
    {
        $apiToken = 'valid_token';
        $payload = [
            'sub' => '00000000000',
            'name' => 'Alex Smith',
            'picture' => 'http://example.com/alexsmith.jpg',
            'given_name' => 'Alex',
            'family_name' => 'Smith',
            'email' => 'alexsmith@example.com',
        ];

        $this->client->method('verifyIdToken')
            ->with($apiToken)
            ->willReturn($payload);

        $existingtUser = new User();
        
        $existingtUser
            ->setGoogleSub($payload['sub'])
            ->setGoogleName('Jane Doe')
            ->setGooglePicture('http://example.com/janedoe.jpg')
            ->setGoogleGivenName('Jane')
            ->setGoogleFamilyName('Doe')
            ->setEmail('janedoe@example.com')
        ;

        $this->userRepository->method('findOneBy')
            ->with(['googleSub' => $payload['sub']])
            ->willReturn($existingtUser);

        $userBadge = $this->googleApiTokenHandler->getUserBadgeFrom($apiToken);
        $user = $userBadge->getUser();

        assert($user instanceof User);
        self::assertInstanceOf(UserBadge::class, $userBadge);
        self::assertEquals($payload['sub'], $user->getGoogleSub());
        self::assertEquals('Alex Smith', $user->getGoogleName());
        self::assertEquals('http://example.com/alexsmith.jpg', $user->getGooglePicture());
        self::assertEquals('Alex', $user->getGoogleGivenName());
        self::assertEquals('Smith', $user->getGoogleFamilyName());
        self::assertEquals('alexsmith@example.com', $user->getEmail());
    }

    public function testExceptionFromInvalidToken(): void
    {
        self::expectException(BadCredentialsException::class);

        $apiToken = 'invalid_token';

        $this->client->method('verifyIdToken')
            ->with($apiToken)
            ->willReturn(false);

        $this->googleApiTokenHandler->getUserBadgeFrom($apiToken);
    }
}
