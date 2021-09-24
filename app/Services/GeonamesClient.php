<?php
namespace App\Services;

use App\Services\RequestClient;

class GeonamesClient
{
    public function getProvinceFromPostalCode($postalCode)
    {
        $response = RequestClient::get(
            sprintf(config('services.geonames.postalcode_endpoint'), str_replace(' ', '', $postalCode))
        );

        return str_replace('-', ' ', json_decode($response)->postalcodes[0]->adminName1) ?? null;
    }
}
