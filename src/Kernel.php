<?php

declare(strict_types=1);

namespace App;

class Kernel
{
    public function __construct(
        private readonly string $rootDir,
    ) {
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function getBaseUrl(?string $url = null): string
    {
        return trim(getenv('BASE_URL'), '/') . '/' . $url;
    }

    public function getVncUrl(): string
    {
        return trim(getenv('CHROME_VNC_URL'), '/');
    }

    public function getTmpDir(): string
    {
        return $this->rootDir . 'tmp/';
    }

    public function getTemplatesDir(): string
    {
        return $this->rootDir . 'templates/';
    }
}
