<?php

namespace App\Services\Scrapers;

class LogiusScraper extends GovernmentPdfAbstract
{
    public function scrape($pages) : array
    {
        $rawData = [];

        $rawData['company_id'] = 1438802;

        return $rawData;
    }
}
