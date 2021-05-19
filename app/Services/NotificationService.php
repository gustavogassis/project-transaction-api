<?php

namespace App\Services;

use App\Models\User;
use Exception;
use GuzzleHttp\Client;

class NotificationService
{
    private Client $client;
    private string $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = config('api.notification_url');
    }

    public function notifyUser(User $payee, float $value): void
    {
        $response = $this->client->request('POST', $this->baseUrl);
        $content = (string) $response->getBody();
        $status = json_decode($content);

        if ($status->message !== 'Success') {
            throw new Exception();
        }
    }
}
