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

        try {
            $this->deleteAllFilesFrom('uploads/');

            $arr_file_types = ['application/pdf'];

            if (!(in_array($_FILES['file']['type'], $arr_file_types))) {
                return 'Wrong file extension!';
            }

            if (!file_exists('uploads')) {
                mkdir('uploads', 0777);
            }

            $filename = time().'_'.$_FILES['file']['name'];
            move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/'.$filename);

            $output = $this->scrapePdfAndSendToCats('uploads/' . $filename);

            return response()->json($output === '' ? 'OK' : $output, $output === '' ? 200 : 500);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function test(Request $request)
    {
        try {
            $filePath = $request->get('file');
            return $this->scrapePdfAndSendToCats('pdfs/' . $filePath, true);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    private function scrapePdfAndSendToCats($filePath, $debug = false)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        $rawData = $this->governmentPdfScraper->scrape($pdf);

        $payload = $this->payloadTransformer->transform($rawData);

        if ($debug) {
            echo '<pre>';
            print_r($rawData);
        } else {
            return $this->catsApiClient->addJob($payload);
        }
    }

    private function deleteAllFilesFrom($dir)
    {
        $files = glob($dir . '*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file)) {
                unlink($file); // delete file
            }
        }
    }
}
