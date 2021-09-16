<?php

namespace App\Http\Controllers;

use App\Services\CatsApiClient;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class HomeController extends Controller
{
    private $catsApiClient;

    public function __construct(CatsApiClient $catsApiClient)
    {
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

        // insert into
        $output = $this->catsApiClient->sendToCatsApi(' {
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

        echo '############################################################'.PHP_EOL;
        print_r($output);
        echo '############################################################'.PHP_EOL;


        return response()->json([
            'status' => 'success',
            'response' => 'OK',
        ], 202);
    }
}
