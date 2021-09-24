<?php

namespace App\Services\Scrapers;

class IvoScraper37 extends GovernmentPdfAbstract
{
    public function scrape($pages) : array
    {
        $rawData = [];

        $rawData['company_id'] = 1005293;

        return $rawData;

    }
}
