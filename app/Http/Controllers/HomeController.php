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
//        echo $pdf->getText();
//        echo '<br /><br />';

        // get text per page
//        $pages  = $pdf->getPages();
//        foreach ($pages as $page) {
//            echo '<pre>';
//            print_r($page->getDataTm());
//        }

        // get details
//        $details  = $pdf->getDetails();
//        print_r($details);

        // echte flow
        $rawData = $this->grabGovernmentPdfData->grab($pdf);
//        print_r($rawData);
        $payload = $this->payloadTransformer->transform($rawData);
        print_r($payload);
        // insert new job into cats
        $output = $this->catsApiClient->addJob($payload);

        echo '############################################################<br />';
        print_r($output);
        echo '############################################################<br />';

        return response()->json([
            'status' => 'success',
            'response' => 'OK',
        ], 202);
    }
}
