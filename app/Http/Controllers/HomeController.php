<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class HomeController extends Controller
{
    public function __construct()
    {

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

        return response()->json([
            'status' => 'success',
            'response' => 'OK',
        ], 202);
    }
}
