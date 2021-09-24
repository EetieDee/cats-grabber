<?php
namespace App\Services;

use App\Services\Scrapers\DictuScraper;
use App\Services\Scrapers\IvoScraper37;
use App\Services\Scrapers\LogiusScraper;
use App\Services\Scrapers\IvoScraper30;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class GovernmentPdfScraper
{
    private $dictuScraper;
    private $logiusScraper;
    private $ivoScraper30;
    private $ivoScraper37;
    private $smalotPdfHelper;

    public function __construct(
        DictuScraper    $dictuScraper,
        LogiusScraper   $logiusScraper,
        IvoScraper30    $ivoScraper30,
        IvoScraper37    $ivoScraper37,
        SmalotPdfHelper $smalotPdfHelper
    ) {
        $this->dictuScraper = $dictuScraper;
        $this->logiusScraper = $logiusScraper;
        $this->ivoScraper30 = $ivoScraper30;
        $this->ivoScraper37 = $ivoScraper37;
        $this->smalotPdfHelper = $smalotPdfHelper;
    }
    public function scrape($pdf)
    {
        $aanvraagInhuurICTPage = $this->smalotPdfHelper->getPageByText($pdf, 'Aanvraag Inhuur ICT');
        $type = $this->smalotPdfHelper->assumeType($aanvraagInhuurICTPage);

//        echo '<pre>';
//        print_r($type);
//        print_r($aanvraagInhuurICTPage->getDataTm());
//        exit;

        Log::debug('type: ' . $type . PHP_EOL);


        switch($type) {
            case SmalotPdfHelper::$TYPE_IVO30:
                return $this->ivoScraper30->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_IVO37:
                return $this->ivoScraper37->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_LOGIUS:
                return $this->logiusScraper->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_DICTU:
                return $this->dictuScraper->scrape($pdf->getPages());
        }
    }
}
