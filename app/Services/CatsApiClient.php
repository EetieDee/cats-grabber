<?php
namespace App\Services;

use Illuminate\Http\Request;

class CatsApiClient
{
    public function sendToCatsApi($jsonPayload) {

        $output = PostClient::send(
            "https://api.catsone.nl/v3/jobs/search?per_page=15&page=1",
            $jsonPayload,
            'f08705f76b7d424b0421b0ca2d48a16d');

        return $output;
    }

}
