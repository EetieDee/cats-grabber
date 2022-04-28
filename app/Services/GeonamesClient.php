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

        $responseArray = json_decode($response);
        if (property_exists($responseArray, 'postalcodes') && array_key_exists(0, $responseArray->postalcodes)) {
            return str_replace('-', ' ', json_decode($response)->postalcodes[0]->adminName1);
        } else {
            return null;
        }
    }
}
