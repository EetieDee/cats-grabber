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
        print_r('hoi');

        $parser = new Parser();
        $pdf = $parser->parseFile('test.pdf');

//        echo $pdf->getText();
//        print_r($pdf->getPages());

        $pages  = $pdf->getPages();

        foreach ($pages as $page) {
            echo '---------------------------------<br />';
            echo $page->getText();
        }

        echo '<br /><br />';
        // Retrieve all details from the pdf file.
        $details  = $pdf->getDetails();

// Loop over each property to extract values (string or array).
        foreach ($details as $property => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            echo $property . ' => ' . $value . "\n";
        }

//        $rawData = $this->grabGovernmentPdfData->grab($pdf);
//        print_r($rawData);
//        $payload = $this->payloadTransformer->transform($rawData);

        // insert into cats
        $output = $this->catsApiClient->addJob(' {
            "and": [{
                "field": "is_published",
                "filter": "exactly",
                "value": true
            }, {
                "field": "status_id",
                "filter": "exactly",
                "value": "185291"
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
