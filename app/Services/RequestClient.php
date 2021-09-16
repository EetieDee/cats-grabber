<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class RequestClient {

    public static function send($url, $jsonPayload, $token, $method = 'POST')
    {
        $request = Http::withHeaders(
            [
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => 'Token ' . $token
            ]
        );

        $payload = [];
        if ($jsonPayload) {
            $payload['body'] = $jsonPayload;
        }

        $response = $request->send(
            $method,
            $url,
            $payload
        );

        return $response->body();
    }
}
