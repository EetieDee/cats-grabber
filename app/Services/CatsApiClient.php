<?php
namespace App\Services;

class CatsApiClient
{
    public function addJob($jsonPayload) {

        return RequestClient::sendJsonWithToken(
            config('catsone.api_endpoint_add_job'),
            $jsonPayload,
            config('catsone.token'),
            'POST',
            'location');

    }

    public function addCustomField($jobId, $customFieldId, $jsonPayload) {

        $url = str_replace('{custom_field_id}', $customFieldId,
                 str_replace('{job_id}', $jobId,
                   config('catsone.api_endpoint_add_custom_field')
                 )
               );

        return RequestClient::sendJsonWithToken(
            $url,
            $jsonPayload,
            config('catsone.token'),
            'PUT');

    }

    public function addAttachment($jobId, $filePath) {

        $filePathArr = explode('_', $filePath);
        array_shift($filePathArr);
        $fileName = implode('_', $filePathArr);

        $url = str_replace('{file_name}', $fileName,
            str_replace('{job_id}', $jobId,
                config('catsone.api_endpoint_add_attachment')
            )
        );

        return RequestClient::sendBinaryWithToken(
            $url,
            config('catsone.token'),
            $fileName,
            $filePath
        );
    }

    public function getContactIdFromName($contactName) {

        $url = str_replace('{contact_name}', $contactName, config('catsone.api_endpoint_get_contact_id'));

        $response = RequestClient::sendJsonWithToken(
            $url,
            null,
            config('catsone.token'),
            'GET');

        $responseObj = json_decode($response);

        if ($responseObj && $responseObj->count > 0) {
            $contacts = $responseObj->_embedded->contacts[0];
            return $contacts->id;
        } else {
            return null;
        }
    }

}
