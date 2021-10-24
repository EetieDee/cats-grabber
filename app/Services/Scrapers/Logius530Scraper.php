<?php

namespace App\Services\Scrapers;

use App\Helpers\ArrayHelper;
use App\Helpers\DateHelper;
use App\Services\GeonamesClient;
use App\Services\SmalotPdfHelper;
use App\Transformers\DescriptionTransformer;

class Logius530Scraper extends GovernmentPdfAbstract
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

            // PAGE 'Aanvraag Inhuur ICT'
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'Referentienummer')) {
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Referentienummer') !== false) {
                        $rawData['referentienr'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 3);
                    }

                    if (strpos($textOfElem, '(FTE) bij deze aanvraag') !== false) {
                        $rawData['openings'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }

                    if (strpos($textOfElem, 'Soort Aanvraag') !== false) {
                        $rawData['notes'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }
                }

                $coordsFromAanvullend = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Achtergrond opdracht*', true);
                $textWithin = $page->getTextXY(132, $coordsFromAanvullend[1] - 125, 10, 125);
                $descriptionToken['aanvullend'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromToelichting = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Opdrachtbeschrijving *', true);
                $textWithin = $page->getTextXY(132, $coordsFromToelichting[1] - 125, 10, 125);
                $descriptionToken['toelichting'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'Indienen offertes*')) {
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Indienen offertes*') !== false) {
                        $rawData['deadline'] = $this->dateHelper->formatDutchDate($this->smalotPdfHelper->getTextByPos($dataTm, $key + 1), 'm-d-Y');
                    }
                }
            }

            // page SELECTIE KWALITEITENPROFIEL
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'SELECTIE KWALITEITENPROFIEL')) {
//                echo '<pre>'; print_r($dataTm); exit;
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Let op! Eisen zijn knock-out criteria') !== false) {
                        $rawData['title'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }

                    if (strpos($textOfElem, 'salarisschaal Rijk*') !== false) {
                        $scale = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                        $rawData['scale'] = $this->arrayHelper->getCatsoneSchaalId($scale);
                    }
                }

            }

            // page INZETGEGEVENS
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'INZETGEGEVENS')) {
                foreach ($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Gewenste startdatum') !== false) {
                        $dutchDate = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['dutch_date'] = $dutchDate;
                        $rawData['start_date'] = $this->dateHelper->formatDutchdate($dutchDate, 'Y-m-d');
                        $rawData['start_date_custom'] = $this->dateHelper->formatDutchdate($dutchDate, 'd-m-Y');
                        $rawData['start_date_header'] = $this->dateHelper->formatDutchdate($dutchDate);
                    }
                    if (strpos($textOfElem, 'Aantal maanden initi') !== false) {
                        $rawData['duration'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key - 1) . ' maanden';
                    }
                    if (strpos($textOfElem, 'Postcode hoofdstandplaats') !== false) {
                        $postalCode = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['postal_code'] = $postalCode;
                        $rawData['state'] = $this->geonamesClient->getProvinceFromPostalCode($postalCode);
                    }

                    if (strpos($textOfElem, 'Naam hoofdstandplaats') !== false) {
                        $rawData['city'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 3);
                    }

                    if (strpos($textOfElem, 'Uren per week') !== false) {
                        $rawData['hours_per_week'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                    }
                }
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'EISEN BIJ DEZE AANVRAAG')) {
//                echo '<pre>'; print_r($dataTm); exit;
                // eisen

//                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Dominant ', true);
//                print_r($coordsFromCompetenties);
//                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Professionele Kennisgebieden*', true);
//                $textWithin = $page->getTextXY(131, $coordsFromCompetenties[1] -40, 25, 40);
//                print_r($textWithin);exit;
//                print_r($coordsFromCompetenties);
//                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Archimate', true);
//                print_r($coordsFromCompetenties);
//                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Bestuurlijke Informatica', true);
//                print_r($coordsFromCompetenties);
//                exit;

                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Dominant ', true);
                $textWithin = $page->getTextXY(131, $coordsFromCompetenties[1] - 35, 25, 35);
                $dominantLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(253, $coordsFromCompetenties[1] - 35, 25, 35);
                $dominantRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_dominant'] = $this->arrayHelper->concatTwoArrays($dominantLeft, $dominantRight, 'Opleiding, Certificaten, Kennisniveau*:');


                $coordsFromAk = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Overige vereiste ', true);
                $textWithin = $page->getTextXY(131, $coordsFromAk[1] - 35, 25, 35);
                $akLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(253, $coordsFromAk[1] - 35, 25, 35);
                $akRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_overige_vereiste'] = $this->arrayHelper->concatTwoArrays($akLeft, $akRight, 'Opleiding, Certificaten, Kennisniveau:');

                $coordsFromWish3 = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Ervaring', true);
                $textWithin = $page->getTextXY(132, $coordsFromWish3[1] - 35, 50, 35);
                $ervaringLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(520, $coordsFromWish3[1] - 35, 20, 35);
                $ervaringRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['ervaring'] = $this->arrayHelper->concatTwoArrays($ervaringLeft, $ervaringRight, 'bewezen aantal jaar');
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'WENSEN BIJ DEZE AANVRAAG')) {

                // wensen
                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Competenties', true);
                $textWithin = $page->getTextXY(132, $coordsFromCompetenties[1] - 50, 10, 50);
                $descriptionToken['wensen_competenties'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromAk = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Aanvullende kennis', true);
                $textWithin = $page->getTextXY(132, $coordsFromAk[1] - 40, 20, 40);
                $descriptionToken['wensen_aanvullende_kennis'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromWish3 = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Overige functiewensen', true);
                $textWithin = $page->getTextXY(132, $coordsFromWish3[1] - 40, 20, 40);
                $descriptionToken['wensen_overige_functiewensen'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
            }

        }

        $descriptionToken['header'] = $this->descriptionTransformer->getHeader($rawData);
        $description = $this->descriptionTransformer->getDescription($descriptionToken);
        $rawData['description'] = $description;

        $rawData['company_id'] = 1438802;

        return array_merge($this->fixedData(), $rawData);

    }
}
