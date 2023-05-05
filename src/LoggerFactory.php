<?php

declare(strict_types=1);

namespace App;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    public static function create(string $name, string $logLevel, string $logStream): Logger
    {
        $formatter = new LineFormatter(dateFormat: 'Y.m.d, H:i:s');

        $errorStream = new StreamHandler($logStream ?: 'php://stderr', $logLevel);
        $errorStream->setFormatter($formatter);

        $infoStream = new StreamHandler($logStream ?: 'php://stdout', $logLevel);
        $infoStream->setFormatter($formatter);

        $logger = new Logger(
            $name,
            [new FilterHandler($infoStream, Logger::DEBUG, Logger::INFO, false), $errorStream]
        );

        return $logger;
    }
}
