<?php

declare(strict_types=1);

namespace App\Tests\ApiResource;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Simulator\GoogleClientSimulator;

abstract class ApiResourceTestCase extends ApiTestCase
{
    /**
     * @var array<string, array<string, string>>
     */
    protected static array $googlePayloads = [];

    protected const FAKED_GOOGLE_SERVICE_CREDETIALS = '{
        "type": "service_account",
        "project_id": "test",
        "private_key_id": "123456789",
        "private_key": "-----BEGIN PRIVATE KEY-----\n123=\n-----END PRIVATE KEY-----\n",
        "client_email": "firebase-adminsdk@gserviceaccount.com",
        "client_id": "123456789",
        "auth_uri": "https://accounts.google.com/o/oauth2/auth",
        "token_uri": "https://oauth2.googleapis.com/token",
        "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
        "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk%40gserviceaccount.com",
        "universe_domain": "googleapis.com"
    }';

    /**
     * Creates an API client and sets up the Google Client Simulator service.
     * We need set up services AFTER the kernel is booted in original method.
     *
     * @param array<mixed> $kernelOptions
     * @param array<mixed> $defaultOptions
     */
    protected static function createClient(
        array $kernelOptions = [],
        array $defaultOptions = ['headers' => ['Content-Type: application/ld+json']],
    ): Client {
        $client = parent::createClient($kernelOptions, $defaultOptions);

        if (empty(static::$googlePayloads)) {
            return $client;
        }

        $simulator = self::getContainer()->get('Google\Client');

        assert($simulator instanceof GoogleClientSimulator);

        $simulator->setPayloads(static::$googlePayloads);

        return $client;
    }

    protected static function createClientWithCredentials(string $token): Client
    {
        return static::createClient(defaultOptions: ['headers' => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/ld+json',
        ]]);
    }
}
