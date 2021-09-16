<?php
namespace App\Services;

use Illuminate\Http\Request;

class CatsApiClient
{
    public function sendToCatsApi($jsonPayload) {

        $output = RequestClient::send(
            config('catsone.api_endpoint_add_job'),
            $jsonPayload,
            config('catsone.token'));

        return $output;
    }

}
