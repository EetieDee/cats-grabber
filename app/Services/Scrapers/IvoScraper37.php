<?php

namespace App\Services\Scrapers;

use App\Helpers\ArrayHelper;
use App\Helpers\DateHelper;
use App\Services\GeonamesClient;
use App\Services\SmalotPdfHelper;
use App\Transformers\DescriptionTransformer;

class IvoScraper37 extends GovernmentPdfAbstract
{
    private $geonamesClient;
    private $dateHelper;
    private $smalotPdfHelper;
    private $descriptionTransformer;
    private $arrayHelper;

    public function __construct(
        GeonamesClient $geonamesClient,
        DateHelper $dateHelper,
        SmalotPdfHelper $smalotPdfHelper,
        DescriptionTransformer $descriptionTransformer,
        ArrayHelper $arrayHelper
    )
    {
        $this->geonamesClient = $geonamesClient;
        $this->dateHelper = $dateHelper;
        $this->smalotPdfHelper = $smalotPdfHelper;
        $this->descriptionTransformer = $descriptionTransformer;
        $this->arrayHelper = $arrayHelper;
    }

    public function scrape($pages) : array
    {
        $rawData = [];

        foreach ($pages as $page) {
            $dataTm = $page->getDataTm();


            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'SELECTIE KWALITEITENPROFIEL')) {
//                echo '<pre>'; print_r($dataTm); exit;
                $textWithin = $page->getTextXY(355, 994, 15, 15);
                $title =  $this->smalotPdfHelper->getAllTextFromDataTm($textWithin)[0] ?? '';
                $descriptionToken['title'] = $title;
                $rawData['title'] = $title;    // JA

                $textWithin = $page->getTextXY(788, 1024, 20, 20);
                $rawData['openings'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin, true);  // JA

                $rawData['scale'] = ''; // bestaat niet in ivo37
            }


            // PAGE 'Aanvraag Inhuur ICT'
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'Aanvraag Inhuur ICT')) {
//                echo '<pre>eee'; print_r($dataTm); exit;
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Referentienummer') !== false) {
                        $rawData['referentienr'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 3);   // JA
                    }


                }
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'Indienen offertes*')) {
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Indienen offertes*') !== false) {
                        $rawData['deadline'] = $this->dateHelper->formatDutchDate($this->smalotPdfHelper->getTextByPos($dataTm, $key + 1),'Y-m-d');  // JA
                        $rawData['deadline_time'] = $this->dateHelper->formatDutchDate($this->smalotPdfHelper->getTextByPos($dataTm, $key + 1), 'H:i');
                    }
                }
            }

            // page OPDRACHT
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'OPDRACHT')) {

//                echo '<pre>hhh'; print_r($dataTm); exit;

                $coordsFromAanvullend = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Achtergrond opdracht*', true);
                $textWithin = $page->getTextXY(350, $coordsFromAanvullend[1] - 30, 25, 35);
                $descriptionToken['aanvullend'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);   // JA

                $coordsFromToelichting = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Opdrachtbeschrijving *:', false);
                $textWithin = $page->getTextXY(350, $coordsFromToelichting[1] - 30, 25, 50);
                $descriptionToken['toelichting'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);  // JA
            }

            // page INZETGEGEVENS
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'INZETGEGEVENS')) {
                // echo '<pre>ggg'; print_r($dataTm); exit;
                foreach ($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Gewenste startdatum') !== false) {
                        $dutchDate = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['dutch_date'] = $dutchDate;
                        $rawData['start_date'] = $this->dateHelper->formatDutchdate($dutchDate, 'Y-m-d');
                        $rawData['start_date_custom'] = $this->dateHelper->formatDutchdate($dutchDate, 'd-m-Y');
                        $rawData['start_date_header'] = $this->dateHelper->formatDutchdate($dutchDate);       // JA
                    }
                    if (strpos($textOfElem, 'Aantal maanden initi') !== false) {
                        $rawData['duration'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key - 1) . ' maanden';   // JA
                    }
                    if (strpos($textOfElem, 'Postcode hoofdstandplaats') !== false) {
                        $postalCode = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['postal_code'] = $postalCode;
                        $rawData['state'] = $this->geonamesClient->getProvinceFromPostalCode($postalCode);    // JA
                    }

                    if (strpos($textOfElem, 'Naam hoofdstandplaats') !== false) {
                        $rawData['city'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 3);    // JA
                    }

                    if (strpos($textOfElem, 'Uren per week') !== false) {
                        $rawData['hours_per_week'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                    }

                    if (strpos($textOfElem, 'Soort Aanvraag') !== false) {
                        $rawData['notes'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }
                }
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'EISEN EN WENSEN')) {   // JAAAAAAAAA
//                echo '<pre>eis'; print_r($dataTm); exit;
                // eisen
                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Algemeen', true);
                $textWithin = $page->getTextXY(367, $coordsFromCompetenties[1] - 30, 25, 40);
                $eisenDominantLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(741, $coordsFromCompetenties[1] - 35, 25, 40);
                $eisenDominantRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_dominant'] = $this->arrayHelper->concatTwoArrays($eisenDominantLeft, $eisenDominantRight, 'bewezen aantal jaar');

                $coordsFromAk = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Vereiste competenties', true);
                $textWithin = $page->getTextXY(367, $coordsFromAk[1] - 25, 25, 35);
                $descriptionToken['eisen_overige_vereiste'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $descriptionToken['ervaring'] = [];

                // wensen
                $textWithin = $page->getTextXY(350, 840, 25, 40);
                $descriptionToken['wensen_competenties'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $textWithin = $page->getTextXY(350, 750, 25, 40);
                $descriptionToken['wensen_aanvullende_kennis'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);


                $descriptionToken['wensen_overige_functiewensen'] = [];

            }
        }

        $descriptionToken['header'] = $this->descriptionTransformer->getHeader($rawData);
        $description = $this->descriptionTransformer->getDescription($descriptionToken);
        $rawData['description'] = $description;

        $rawData['company_id'] = 1005293;
        return array_merge($this->fixedData(), $rawData);

    }
}
