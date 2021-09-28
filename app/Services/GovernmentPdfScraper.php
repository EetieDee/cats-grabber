<?php
namespace App\Services;

use App\Services\Scrapers\Dictu530Scraper;
use App\Services\Scrapers\Dictu850Scraper;
use App\Services\Scrapers\IvoScraper37;
use App\Services\Scrapers\Logius530Scraper;
use App\Services\Scrapers\Logius850Scraper;
use App\Services\Scrapers\IvoScraper30;
use Illuminate\Support\Facades\Log;

class GovernmentPdfScraper
{
    private $dictu530Scraper;
    private $dictu850Scraper;
    private $logius530Scraper;
    private $logius850Scraper;
    private $ivoScraper30;
    private $ivoScraper37;
    private $smalotPdfHelper;

    public function __construct(
        Dictu530Scraper $dictu530Scraper,
        Dictu850Scraper $dictu850Scraper,
        Logius530Scraper $logius530Scraper,
        Logius850Scraper $logius850Scraper,
        IvoScraper30    $ivoScraper30,
        IvoScraper37    $ivoScraper37,
        SmalotPdfHelper $smalotPdfHelper
    ) {
        $this->dictu530Scraper = $dictu530Scraper;
        $this->dictu850Scraper = $dictu850Scraper;
        $this->logius530Scraper = $logius530Scraper;
        $this->logius850Scraper = $logius850Scraper;
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
                return 'Sorry, deze versie wordt niet ondersteund.';
            case SmalotPdfHelper::$TYPE_IVO37:
                return $this->ivoScraper37->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_LOGIUS850:
                return $this->logius850Scraper->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_LOGIUS530:
                return $this->logius530Scraper->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_DICTU530:
                return $this->dictu530Scraper->scrape($pdf->getPages());
            case SmalotPdfHelper::$TYPE_DICTU850:
                return $this->dictu850Scraper->scrape($pdf->getPages());
        }
    }
}
