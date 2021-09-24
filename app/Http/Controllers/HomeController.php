<?php

namespace App\Http\Controllers;

use App\Services\CatsApiClient;
use App\Services\GovernmentPdfScraper;
use App\Services\SmalotPdfHelper;
use App\Transformers\PayloadTransformer;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class HomeController extends Controller
{
    private $governmentPdfScraper;
    private $payloadTransformer;
    private $catsApiClient;

    public function __construct(
        GovernmentPdfScraper $governmentPdfScraper,
        PayloadTransformer   $payloadTransformer,
        CatsApiClient        $catsApiClient)
    {
        $this->governmentPdfScraper = $governmentPdfScraper;
        $this->payloadTransformer = $payloadTransformer;
        $this->catsApiClient = $catsApiClient;
    }

    public function test(Request $request)
    {
//        $filePath = 'test.pdf';
        $filePath = 'pdfs/dictu/dictu1.pdf';

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        // get full text
//        echo $pdf->getText();
//        echo '<br /><br />';

        // get text per page
//        $pages  = $pdf->getPages();
//        foreach ($pages as $k => $page) {
//            echo '<pre>';
//            print($k);
//            print_r($page->getDataTm());
////            print_r($page->getTextXY(820, 1198, 20, 20));
//            break;
//        }

        // get details
//        $details  = $pdf->getDetails();
//        print_r($details);

        // echte flow
        $rawData = $this->governmentPdfScraper->scrape($pdf);
        echo '<pre>'; print_r($rawData); exit;
        $payload = $this->payloadTransformer->transform($rawData);
        print_r($payload);

        // insert new job into cats
        // $output = $this->catsApiClient->addJob($payload);
//        print_r($output);

        return response()->json([
            'status' => 'success',
            'response' => 'OK',
        ], 202);
    }
}
