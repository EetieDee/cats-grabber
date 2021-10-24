<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class RequestClient {

    public static function sendJsonWithToken($url, $jsonPayload, $token, $method = 'POST', $returnHeader = false) : string
    {
        try {
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

            // oh oh..
            if ($response->body() !== '') {
                return $response->body();
            }

            return $returnHeader ? $response->header($returnHeader) : $response->body();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public static function sendBinaryWithToken($url, $token, $fileName, $filePath) : string
    {
        $request = Http::withHeaders(
            [
                'Content-Type' => 'application/octet-stream',
                'Authorization' => 'Token ' . $token
            ]
        );

        $response = $request->attach(
            'data-binary', file_get_contents($filePath), $fileName
        )->post($url, [
            'fileName' => $fileName,
        ]);

        return $response->body();
    }

    public static function get($url)
    {
        return Http::get($url);
    }
}
