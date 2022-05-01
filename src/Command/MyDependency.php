<?php

declare(strict_types=1);
namespace App\Command;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class MyDependency
{
    private $logger;
    private $psrLogger;

    public function __construct(
        Logger $logger,
        //LoggerInterface $psrLogger
    )
    {
        $this->logger = $logger;
        //$this->psrLogger = $psrLogger;
        $this->logger->pushHandler(new StreamHandler('log/import-xml.log'));
    }

    public function doStuff($logdata)
    {
        $this->logger->info($logdata);
    }

    public function info($logdata)
    {
        $this->logger->info($logdata);
    }

    public function error($logdata)
    {
        $this->logger->error($logdata);
    }
}