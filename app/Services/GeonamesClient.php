<?php
namespace App\Services;

use App\Services\RequestClient;

class GeonamesClient
{
    public function getProvinceFromPostalCode($postalCode)
    {
        if (!preg_match("/^[1-9][0-9]{3} ?(?!sa|sd|ss)[a-z]{2}$/i", $postalCode)) {
            return 'VOER PROVINCIE IN';
        }

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
