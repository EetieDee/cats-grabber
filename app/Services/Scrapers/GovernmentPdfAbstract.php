<?php
namespace App\Services\Scrapers;

abstract class GovernmentPdfAbstract
{
    abstract protected function scrape($pages) : array;

    public function fixedData() : array {
        $data = [];

        // fixed data
        $data['country_code'] = 'NL';
        $data['is_hot'] = true;
        $data['max_rate'] = 'Marktconform';
        $data['is_published'] = false;

        // empty
        $data['department_id'] = '';
        $data['recruiter_id'] = '';
        $data['owner_id'] = '';
        $data['category_name'] = '';
        $data['salary'] = '';
        $data['type'] = '';
        $data['external_id'] = '';
        $data['notes'] = '';
        $data['contact_id'] = '';
        $data['workflow_id'] = '';

        return $data;
    }
}
