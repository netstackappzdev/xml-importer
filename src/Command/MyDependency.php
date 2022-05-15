<?php

declare(strict_types=1);

namespace App\Command;
use Psr\Log\LoggerInterface;

class MyDependency
{

    public function __construct(
        private LoggerInterface $logger
    )
    {
    }

    public function doStuff($logdata): void
    {
        $this->logger->info($logdata);
    }

    public function info($logdata): void
    {
        $this->logger->info($logdata);
    }

    public function error($logdata): void
    {
        $this->logger->error($logdata);
    }
}