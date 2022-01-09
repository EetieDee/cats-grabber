<?php

namespace App\Services\Scrapers;

use App\Helpers\ArrayHelper;
use App\Helpers\DateHelper;
use App\Services\GeonamesClient;
use App\Services\SmalotPdfHelper;
use App\Transformers\DescriptionTransformer;

class IvoScraper30 extends GovernmentPdfAbstract
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
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'Aanvraag Inhuur ICT')) {
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Referentienummer') !== false) {
                        $rawData['notes'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 3);
                    }

                    if (strpos($textOfElem, '(FTE) bij deze aanvraag') !== false) {
                        $rawData['openings'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }
                }
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'Indienen offertes*')) {
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Indienen offertes*') !== false) {
                        $rawData['deadline'] = $this->dateHelper->formatDutchDate($this->smalotPdfHelper->getTextByPos($dataTm, $key + 1), 'Y-m-d');
                    }
                }
            }

            // page OPDRACHT
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'OPDRACHT')) {
//                                echo '<pre>'; print_r($dataTm); exit;
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];
//
//                    if (strpos($textOfElem, 'Let op! Eisen zijn knock-out criteria') !== false) {
//                        $rawData['title'] = '(TEST KAI) '. $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
//                    }
                }

                $coordsFromFunctienaam = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Achtergrond opdracht*', true);
                $textWithin = $page->getTextXY(344, $coordsFromFunctienaam[1] - 4, 10, 12);
                $descriptionToken['aanvullend'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromToelichting = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Opdrachtbeschrijving *:');
                $textWithin = $page->getTextXY(344, $coordsFromToelichting[1] + 10, 10, 10);
                $descriptionToken['toelichting'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

            }

            // page SELECTIE KWALITEITENPROFIEL
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'SELECTIE KWALITEITENPROFIEL')) {
                               // echo '<pre>'; print_r($dataTm); exit;

                $coordsFromFunctienaam = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Functienaam (roepnaam)', true);
                $textWithin = $page->getTextXY(170, $coordsFromFunctienaam[1] - 50, 12, 12);
                $title =  $this->smalotPdfHelper->getAllTextFromDataTm($textWithin)[0] ?? '';
                $descriptionToken['title'] = $title;
                $rawData['title'] = $title;
            }

            // page INZETGEGEVENS
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'INZETGEGEVENS')) {
                foreach ($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Gewenste startdatum') !== false) {
                        $dutchDate = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['dutch_date'] = $dutchDate;
                        $rawData['start_date'] = $this->dateHelper->formatDutchdate($dutchDate, 'd-m-Y');
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

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'EISEN EN WENSEN')) {
                //echo '<pre>'; print_r($dataTm); exit;
                // eisen
                $textWithin = $page->getTextXY(344, 1039 - 70, 10, 70);
                $eisenDominantLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(334, 1028 + 5, 10, 5);
                $eisenDominantRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_dominant'] = $this->arrayHelper->concatTwoArrays($eisenDominantLeft, $eisenDominantRight);

                $textWithin = $page->getTextXY(344, 867 - 25, 10, 25);
                $eisenOverigeVereisteLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(334, 1034 + 2, 10, 2);
                $eisenOverigeVereisteRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_overige_vereiste'] = $this->arrayHelper->concatTwoArrays($eisenOverigeVereisteLeft, $eisenOverigeVereisteRight);

                $textWithin = $page->getTextXY(344, 867 - 25, 10, 25);
                $eisenVereisteKennisgebiedenLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(344, 1034 + 2, 10, 2);
                $eisenVereisteKennisgebiedenRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_derde'] = $this->arrayHelper->concatTwoArrays($eisenVereisteKennisgebiedenLeft, $eisenVereisteKennisgebiedenRight);

                $textWithin = $page->getTextXY(344, 867 - 25, 10, 25);
                $eisenVierdeLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(344, 1034 + 2, 10, 2);
                $eisenvierdeRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['eisen_vierde'] = $this->arrayHelper->concatTwoArrays($eisenVierdeLeft, $eisenvierdeRight);



                $textWithin = $page->getTextXY(211, $coordsFromWish3[1] - 25, 25, 25);
                $descriptionToken['ervaring'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(518, $coordsFromWish3[1] - 25, 25, 25);
                $descriptionToken['ervaring_right'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                // todo nog vierde
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'WENSEN BIJ DEZE AANVRAAG')) {

                // wensen
                $textWithin = $page->getTextXY(344, 572 - 33, 10, 33);
                $descriptionToken['wensen_competenties'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $textWithin = $page->getTextXY(344, 464 - 33, 20, 33);
                $wensenAanvullendeKennisLeft = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                // todo: nakijken of tweede kolom aanvullende kennis goed is
                $textWithin = $page->getTextXY(655, 464 - 33, 20, 33);
                $wensenAanvullendeKennisRight = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $descriptionToken['wensen_aanvullende_kennis'] = $this->arrayHelper->concatTwoArrays($wensenAanvullendeKennisLeft, $wensenAanvullendeKennisRight);

                $textWithin = $page->getTextXY(344, 382 - 33, 20, 33);
                $descriptionToken['wensen_overige_functiewensen'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
            }

        }

        $descriptionToken['header'] = $this->descriptionTransformer->getHeader($rawData);
        $description = $this->descriptionTransformer->getDescription($descriptionToken);
        $rawData['description'] = $description;

        $rawData['company_id'] = 1005293;

        return array_merge($this->fixedData(), $rawData);

    }
}
