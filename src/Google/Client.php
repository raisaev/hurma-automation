<?php

declare(strict_types=1);

namespace App\Google;

use Google\Client as GoogleClient;
use Google\Service\Sheets;

class Client
{
    public function __construct(
        private readonly GoogleClient $client = new GoogleClient()
    ) {
        $this->client->setScopes([Sheets::SPREADSHEETS]);
        $this->client->useApplicationDefaultCredentials();
    }

    public function getGoogleClient(): GoogleClient
    {
        return $this->client;
    }
}
