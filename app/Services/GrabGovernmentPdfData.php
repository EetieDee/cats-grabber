<?php
namespace App\Services;

class GrabGovernmentPdfData
{
    public function grab($pdf)
    {
        $rawData = [];
        $pages  = $pdf->getPages();
        foreach ($pages as $page) {
            $pageText = $page->getText();
            $pageDataTokens = $page->getDataTm();

            // page Aanvraag Inhuur ICT
            if (strpos($pageText, 'Aanvraag Inhuur ICT') !== false) {
                foreach($pageDataTokens as $key => $data) {
                    if (strpos($data[1], 'Referentienummer') !== false) {
                        $rawData['title'] = $pageDataTokens[$key + 3][1];
                    }
                }
            }

            // page SELECTIE KWALITEITENPROFIEL
            if (strpos($pageText, 'SELECTIE KWALITEITENPROFIEL') !== false) {
                foreach($pageDataTokens as $key => $data) {
                    if (strpos($data[1], 'Functieschaal') !== false) {
                        $rawData['salary'] = $pageDataTokens[$key + 3][1];
                    }
                }
            }

            // page INZETGEGEVENS
            if (strpos($pageText, 'INZETGEGEVENS') !== false) {
                foreach ($pageDataTokens as $key => $data) {
                    if (strpos($data[1], 'Gewenste startdatum') !== false) {
                        $rawData['start_date'] = date('Y-m-d'); //$pageDataTokens[$key + 2][1];
                    }
                    if (strpos($data[1], 'Initiële einddatum') !== false) {
                        // $rawData['duration'] = $pageDataTokens[$key + 2][1];   // todo duration waarschijnlijk maand. berekenen!
                    }
                    if (strpos($data[1], 'Postcode hoofdstandplaats') !== false) {
                        $rawData['postal_code'] = $pageDataTokens[$key + 2][1];
                    }

                    if (strpos($data[1], 'Naam hoofdstandplaats') !== false) {
                        $rawData['city'] = $pageDataTokens[$key + 2][1];
                    }

                }
            }
        }


//        $rawData['title'] = '';
//        $rawData['city'] = '';
        $rawData['state'] = '';
//        $rawData['postal_code'] = '';
//        $rawData['country_code'] = '';
        $rawData['company_id'] = 7;
        $rawData['department_id'] = '';
        $rawData['recruiter_id'] = '';
        $rawData['owner_id'] = '';
        $rawData['category_name'] = '';  // category name?
        $rawData['is_hot'] = '';
//        $rawData['start_date'] = '';
//        $rawData['salary'] = '';
        $rawData['max_rate'] = '';   // maximum tarief?
        $rawData['duration'] = '';
        $rawData['type'] = '';    // type?
        $rawData['openings'] = '';   // openings?
        $rawData['external_id'] = '';
        $rawData['description'] = '';   // description?
        $rawData['notes'] = '';
        $rawData['contact_id'] = '';
        $rawData['workflow_id'] = '';
        $rawData['recruiter_id'] = '';

        return array_merge($rawData, $this->fixedRawData());
    }

    private function fixedRawData()
    {
        $fixedRawData = [];

        // empty
        $fixedRawData['state'] = 'ZH';   // todo get state from postal code

        // fixed data
        $fixedRawData['country_code'] = 'NL';

        return $fixedRawData;
    }
}
