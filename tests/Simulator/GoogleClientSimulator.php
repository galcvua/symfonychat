<?php

declare(strict_types=1);

namespace App\Tests\Simulator;

use Google\Client as GoogleClient;

class GoogleClientSimulator extends GoogleClient
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $payloads = [];

    /**
     * @return array<string, string>|false
     */
    public function verifyIdToken($idToken = null): array|false
    {
        if ($idToken === null || !array_key_exists($idToken, $this->payloads)) {
            return false;
        }

        return $this->payloads[$idToken];
    }

    /**
     * @param array<string, array<string, string>> $payloads
     */
    public function setPayloads(array $payloads): void
    {
        $this->payloads = $payloads;
    }
}
