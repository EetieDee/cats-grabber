<?php
namespace App\Services;

class CatsApiClient
{
    public function addJob($jsonPayload) {

        $output = RequestClient::send(
            config('catsone.api_endpoint_add_job'),
            $jsonPayload,
            config('catsone.token'));

        return $output;
    }

}
