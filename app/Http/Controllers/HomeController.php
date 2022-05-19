<?php

namespace App\Http\Controllers;

use App\Services\CatsApiClient;
use App\Services\GovernmentPdfScraper;
use App\Services\SmalotPdfHelper;
use App\Transformers\CustomFieldTransformer;
use App\Transformers\PayloadTransformer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Smalot\PdfParser\Parser;

class HomeController extends Controller
{
    private $governmentPdfScraper;
    private $payloadTransformer;
    private $customFieldTransformer;
    private $catsApiClient;

    public function __construct(
        GovernmentPdfScraper $governmentPdfScraper,
        PayloadTransformer   $payloadTransformer,
        CustomFieldTransformer $customFieldTransformer,
        CatsApiClient        $catsApiClient)
    {
        $this->governmentPdfScraper = $governmentPdfScraper;
        $this->payloadTransformer = $payloadTransformer;
        $this->customFieldTransformer = $customFieldTransformer;
        $this->catsApiClient = $catsApiClient;
    }

    public function index(Request $request) {
        if(!$this->checkAuthentication($request)) {
            return;
        }
        return Inertia::render('Home');
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

            return $output;

        } catch (\Exception $e) {
            return $e->getMessage().':::'.$e->getTraceAsString();
        }
    }

    public function test(Request $request)
    {
        if(!$this->checkAuthentication($request)) {
            return;
        }
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

        // save file
//        $fp = fopen('pdfs/uploads/uploads.txt', 'a');
//        fwrite($fp, $filePath.PHP_EOL);
//        fclose($fp);
//        file_put_contents(
//            $filePath,
//            file_get_contents($filePath)
//        );

        $rawData = $this->governmentPdfScraper->scrape($pdf);

        $payload = $this->payloadTransformer->transform($rawData);

        if ($debug) {
            echo '<pre>';
            print_r($rawData);
            exit;
        } else {
            $output = $this->catsApiClient->addJob($payload);
        }

        if (strpos($output, 'api.catsone.nl')) {

            $link = str_replace('https://api.catsone.nl/v3/jobs/', 'https://ukomst.catsone.nl/index.php?m=joborders&a=show&jobOrderID=', $output);
            $outputMsg = "Gelukt! Ga naar: <br/> <a href='".$link."' target='_blank'>".$link."</a><br /><br /><a href='/?token=".config('app.secret')."'>Nog een vacature importeren</a>";
            $jobId = $this->getJobIdFromUrl($output);

            // OK, add custom fields
            // '20771' => 'schaal',
            $customFields = [
                '18500' => 'start_date_custom',
                '20091' => 'deadline',
                '18518' => 'hours_per_week',
                '37646' => 'referentienr',
                '20771' => 'scale',
                '39907' => 'deadline',
                '39922' => 'deadline_time',
                '20561' => 'option_on_renewal',
                '20732' => 'end_customer'
            ];

            foreach ($customFields as $customFieldId => $rawDataName) {
                $payload = $this->customFieldTransformer->transform($rawData[$rawDataName]);
                $this->catsApiClient->addCustomField($jobId, $customFieldId, $payload);
            }

            // add attachment
            $this->catsApiClient->addAttachment($jobId, $filePath);
        } else {
            $outputMsg = "Oops! Deze vacature bestaat mogelijk al (controleer in CATS) of er is een fout opgetreden.. ".$output."<br /><br /><a href='/?token=".config('app.secret')."'>Overnieuw proberen</a>";
        }

        return $outputMsg;
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

    private function checkAuthentication(Request $request)
    {
        $token = config('app.secret');
        if ($request->has('token') && $request->get('token') === $token) {
            return true;
        }

        return false;
    }

    /**
     * @param string $output
     * @return mixed|string
     */
    private function getJobIdFromUrl(string $output)
    {
        $outputArr = explode('/', $output);
        return $outputArr[count($outputArr) - 1];
    }
}
