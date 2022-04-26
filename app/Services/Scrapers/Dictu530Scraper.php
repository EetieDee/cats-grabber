<?php

namespace App\Services\Scrapers;

use App\Helpers\ArrayHelper;
use App\Helpers\DateHelper;
use App\Services\CatsApiClient;
use App\Services\GeonamesClient;
use App\Services\SmalotPdfHelper;
use App\Transformers\DescriptionTransformer;

class Dictu530Scraper extends GovernmentPdfAbstract
{
    private $geonamesClient;
    private $dateHelper;
    private $smalotPdfHelper;
    private $descriptionTransformer;
    private $arrayHelper;
    private $catsApiClient;

    public function __construct(
        GeonamesClient $geonamesClient,
        DateHelper $dateHelper,
        SmalotPdfHelper $smalotPdfHelper,
        DescriptionTransformer $descriptionTransformer,
        ArrayHelper $arrayHelper,
        CatsApiClient $catsApiClient
    )
    {
        $this->geonamesClient = $geonamesClient;
        $this->dateHelper = $dateHelper;
        $this->smalotPdfHelper = $smalotPdfHelper;
        $this->descriptionTransformer = $descriptionTransformer;
        $this->arrayHelper = $arrayHelper;
        $this->catsApiClient = $catsApiClient;
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
//                    echo '<pre>'; print_r($dataTm); exit;
                    if (strpos($textOfElem, 'Referentienummer') !== false) {
                        $rawData['referentienr'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 3);
                    }

                    if (strpos($textOfElem, '(FTE) bij deze aanvraag') !== false) {
                        $rawData['openings'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }

                    if (strpos($textOfElem, 'Indienen offertes*') !== false) {
                        $rawData['deadline'] = $this->dateHelper->formatDutchDate($this->smalotPdfHelper->getTextByPos($dataTm, $key + 1), 'Y-m-d');
                        $rawData['deadline_time'] = trim(preg_replace("/[^\d:]/", '', $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2)));
                    }

                    if (strpos($textOfElem, 'Soort Aanvraag') !== false) {
                        $rawData['notes'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }

                    if (strpos($textOfElem, 'Inhurend manager') !== false) {
                        $contactName = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                        if ($contactName) {
                            $rawData['contact_id'] = $this->catsApiClient->getContactIdFromName($contactName);
                        }
                    }
                }

                $coordsFromAanvullend = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Achtergrond opdracht*', true);
                $textWithin = $page->getTextXY(132, $coordsFromAanvullend[1] - 65, 10, 65);
                $descriptionToken['aanvullend'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromToelichting = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Opdrachtbeschrijving *', true);
                $textWithin = $page->getTextXY(132, $coordsFromToelichting[1] - 65, 10, 65);
                $descriptionToken['toelichting'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
            }

            // page SELECTIE KWALITEITENPROFIEL
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'SELECTIE KWALITEITENPROFIEL')) {
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    $functienaam = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Functienaam (roepnaam)', true);
                    $textWithin = $page->getTextXY(10, $functienaam[1], 150, 10);
                    $rawData['title'] = $textWithin[1][1];

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

                    if (strpos($textOfElem, 'Optie op verlenging') !== false) {
                        $optionOnRenewalText = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $optionOnRenewalIds = ['ja' => 173243, 'nee' => 173246];
                        $rawData['option_on_renewal'] = key_exists(strtolower($optionOnRenewalText), $optionOnRenewalIds) ? $optionOnRenewalIds[strtolower($optionOnRenewalText)] : '';
                    }

                    if (strpos($textOfElem, 'Postcode hoofdstandplaats') !== false) {
                        $postalCode = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['postal_code'] = $postalCode;
                        $rawData['state'] = $this->geonamesClient->getProvinceFromPostalCode($postalCode);
                    }

                    if (strpos($textOfElem, 'Naam hoofdstandplaats') !== false) {
                        $rawData['city'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                    }

                    if (strpos($textOfElem, 'Uren per week') !== false) {
                        $rawData['hours_per_week'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                    }

                    if (strpos($textOfElem, 'Extra standplaats(en)') !== false) {
                        $coordsFromToelichting = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Extra standplaats(en)', true);
                        $textWithin = $page->getTextXY(300,$coordsFromToelichting[1], 50, 10);
                        if (array_key_exists('0', $textWithin) && array_key_exists('1', $textWithin[0]) && $textWithin[0][1]) {
                            $rawData['notes'] .= '<br />'.$textWithin[0][1];
                        }
                    }
                }
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'EISEN BIJ DEZE AANVRAAG')) {
//                echo '<pre>'; print_r($dataTm); exit;
                // eisen
                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Dominant ', true);
                $textWithin = $page->getTextXY(253, $coordsFromCompetenties[1] - 35, 10, 35);
                $descriptionToken['eisen_dominant'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);


                $coordsFromAk = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Overige vereiste ', true);
                $textWithin = $page->getTextXY(253, $coordsFromAk[1] - 45, 10, 45);
                $descriptionToken['eisen_overige_vereiste'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromWish3 = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Ervaring', true);

                $textWithin = $page->getTextXY(132, $coordsFromWish3[1] - 45, 25, 45);
                $ervaringLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(518, $coordsFromWish3[1] - 45, 25, 45);
                $ervaringRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['ervaring'] = $this->arrayHelper->concatTwoArrays($ervaringLeft, $ervaringRight, 'bewezen aantal jaar');
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'WENSEN BIJ DEZE AANVRAAG')) {

                // wensen
                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Competenties', true);
                $textWithin = $page->getTextXY(132, $coordsFromCompetenties[1] - 80, 10, 80);
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

        // fixed data Dictu
        $rawData['company_id'] = 1193352;
        $rawData['end_customer'] = 'DICTU';

        return array_merge($this->fixedData(), $rawData);
    }

}
