<?php

namespace Database\Factories;

use Carbon\Carbon;
use Laravel\Passport\Client;
use Laravel\Passport\PersonalAccessTokenFactory;

class MultiClientPersonalAccessTokenFactory extends PersonalAccessTokenFactory
{
    public function handle(
        $clientName,
        $userId,
        string $name,
        array $scopes = []
    ) {
        $response = new \stdClass();

        $auth_server_response = $this->dispatchRequestToAuthorizationServer(
            $this->createRequest($this->getClient($clientName), $userId, $scopes)
        );

        $response->token_type = $auth_server_response['token_type'];
        $response->access_token = $auth_server_response['access_token'];
        $response->expires_in = $auth_server_response['expires_in'];

        $token = tap($this->findAccessToken($auth_server_response), function ($token) use ($userId, $name) {
            $this->tokens->save($token->forceFill([
                'user_id' => $userId,
                'name' => $name,
            ]));
        });
        $response->created_at = Carbon::parse($token->created_at)->format('Y-m-d h:i:s');
        $response->expires_at = Carbon::parse($token->expires_at)->format('Y-m-d h:i:s');

        return $response;
    }

    protected function getClient(string $name): ?Client
    {
        return Client::firstWhere('name', $name);
    }
}
