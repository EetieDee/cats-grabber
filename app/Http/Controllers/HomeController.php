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

    public function drop(Request $request) {
        $arr_file_types = ['application/pdf'];

        if (!(in_array($_FILES['file']['type'], $arr_file_types))) {
            echo 'NO'; exit;
            return;
        }

        if (!file_exists('uploads')) {
            mkdir('uploads', 0777);
        }

        $filename = time().'_'.$_FILES['file']['name'];

        move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/'.$filename);

        return $this->test('uploads/' . $filename);

    }

    public function test($filePath)
    {
//        $filePath = 'test.pdf';
//        $filePath = 'pdfs/'.$request->get('file');

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
//        echo '<pre>'; print_r($rawData); exit;
        $payload = $this->payloadTransformer->transform($rawData);
//        print_r($payload);

        // insert new job into cats
        $output = $this->catsApiClient->addJob($payload);

        return response()->json($output === '' ? 'OK' : $output, 202);
    }
}
