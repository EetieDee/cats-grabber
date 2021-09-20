<?php

namespace App\Http\Controllers;

use App\Services\CatsApiClient;
use App\Services\GrabGovernmentPdfData;
use App\Transformers\PayloadTransformer;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class HomeController extends Controller
{
    private $grabGovernmentPdfData;
    private $payloadTransformer;
    private $catsApiClient;

    public function __construct(
        GrabGovernmentPdfData $grabGovernmentPdfData,
        PayloadTransformer $payloadTransformer,
        CatsApiClient $catsApiClient)
    {
        $this->grabGovernmentPdfData = $grabGovernmentPdfData;
        $this->payloadTransformer = $payloadTransformer;
        $this->catsApiClient = $catsApiClient;
    }

    public function test(Request $request)
    {
        $file = 'test.pdf';

        $parser = new Parser();
        $pdf = $parser->parseFile($file);

        // get full text
        echo $pdf->getText();
        // print_r($pdf->getPages());
        echo '<br /><br />';

        // get text per page
        $pages  = $pdf->getPages();
        foreach ($pages as $page) {
            echo '---------------------------------<br />';
            echo $page->getText();
        }
        echo '<br /><br />';

        // get details
        $details  = $pdf->getDetails();
        // Loop over each property to extract values (string or array).
        foreach ($details as $property => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            echo $property . ' => ' . $value . "\n";
        }

        // echte flow
//        $rawData = $this->grabGovernmentPdfData->grab($pdf);
//        print_r($rawData);
//        $payload = $this->payloadTransformer->transform($rawData);

        // insert new job into cats
        $output = $this->catsApiClient->addJob('{
           "title":"Chief Operations Technician",
           "location":{
              "city":"Wittingland",
              "state":"IN",
              "postal_code":"30349-0254"
           },
           "country_code":"US",
           "company_id":3526,
           "department_id":7084,
           "recruiter_id":6043,
           "owner_id":9988,
           "category_name":"",
           "is_hot":true,
           "start_date":"2019-03-07T19:39:31.693Z",
           "salary":"",
           "max_rate":"",
           "duration":"",
           "type":"",
           "openings":0,
           "external_id":"",
           "description":"",
           "notes":"",
           "contact_id":8994,
           "workflow_id":3712,
           "custom_fields":[
              {
                 "id":8373,
                 "value":"lorem"
              }
           ]
        }');

        echo '############################################################<br />';
        print_r($output);
        echo '############################################################<br />';

        return response()->json([
            'status' => 'success',
            'response' => 'OK',
        ], 202);
    }
}
