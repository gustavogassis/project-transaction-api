<?php

namespace App\Services;

use GuzzleHttp\Client;

class AuthorizationService
{
    private Client $client;
    private string $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = config('api.authorization_url');
    }

    public function checkTransactionAuthorization($payerId, $payeeId, $value): bool
    {
        $response = $this->client->request('GET', $this->baseUrl);
        $content = (string) $response->getBody();
        $status = json_decode($content);

        return $status->message === "Autorizado";
    }
}
