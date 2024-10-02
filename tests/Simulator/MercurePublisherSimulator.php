<?php

declare(strict_types=1);

namespace App\Tests\Simulator;

use Symfony\Component\Mercure\Update;

class MercurePublisherSimulator
{
    /**
     * @var (callable(Update): string)|null
     */
    private $publisher;

    public function __invoke(Update $update): string
    {
        if ($this->publisher === null) {
            return '';
        }

        return ($this->publisher)($update);
    }

    public function setPublisher(callable $publisher): void
    {
        $this->publisher = $publisher;
    }
}
