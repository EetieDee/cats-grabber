<?php

namespace App\Services\Scrapers;

use App\Services\DateHelper;
use App\Services\GeonamesClient;
use App\Services\SmalotPdfHelper;

class DictuScraper extends GovernmentPdfAbstract
{
    private $geonamesClient;
    private $dateHelper;
    private $smalotPdfHelper;

    public function __construct(
        GeonamesClient $geonamesClient,
        DateHelper $dateHelper,
        SmalotPdfHelper $smalotPdfHelper
    )
    {
        $this->geonamesClient = $geonamesClient;
        $this->dateHelper = $dateHelper;
        $this->smalotPdfHelper = $smalotPdfHelper;
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

                    if (strpos($textOfElem, 'Indienen offertes*') !== false) {
                        $rawData['deadline'] = $this->dateHelper->formatDutchDate($this->smalotPdfHelper->getTextByPos($dataTm, $key + 1));
                    }

                }

            }

            // page SELECTIE KWALITEITENPROFIEL
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'SELECTIE KWALITEITENPROFIEL')) {
//                echo '<pre>'; print_r($dataTm); exit;
                foreach($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Let op! Eisen zijn knock-out criteria') !== false) {
                        $rawData['title'] = '(TEST KAI) '. $this->smalotPdfHelper->getTextByPos($dataTm, $key + 1);
                    }
                }

                $coordsFromAanvullend = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Aanvullende kennisgebieden/Voorbeelden van opleidingen', true);
                $textWithin = $page->getTextXY(132, $coordsFromAanvullend[1] - 40, 10, 35);
                $rawData['description_aanvullend'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromToelichting = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Toelichting', true);
                $textWithin = $page->getTextXY(132, $coordsFromToelichting[1] - 40, 10, 35);
                $rawData['description_toelichting'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

            }

            // page INZETGEGEVENS
            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'INZETGEGEVENS')) {
                foreach ($dataTm as $key => $currentTm) {
                    $textOfElem = $currentTm[1];

                    if (strpos($textOfElem, 'Gewenste startdatum') !== false) {
                        $dutchDate = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                        $rawData['dutch_date'] = $dutchDate;
                        $rawData['start_date'] = $this->dateHelper->formatDutchdate($dutchDate);
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
                        $rawData['city'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                    }

                    if (strpos($textOfElem, 'Uren per week') !== false) {
                        $rawData['hours_per_week'] = $this->smalotPdfHelper->getTextByPos($dataTm, $key + 2);
                    }
                }
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'EISEN BIJ DEZE AANVRAAG')) {
//                echo '<pre>'; print_r($dataTm); exit;
                // eisen
                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Dominant ', true);
                $textWithin = $page->getTextXY(253, $coordsFromCompetenties[1] - 25, 10, 25);
                $rawData['description_eisen_dominant'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);


                $coordsFromAk = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Overige vereiste ', true);
                $textWithin = $page->getTextXY(253, $coordsFromAk[1] - 25, 10, 25);
                $rawData['description_eisen_overige_vereiste'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromWish3 = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Ervaring', true);
                $textWithin = $page->getTextXY(132, $coordsFromWish3[1] - 25, 10, 25);
                $rawData['description_ervaring_left'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
                $textWithin = $page->getTextXY(500, $coordsFromWish3[1] - 25, 10, 25);
                $rawData['description_ervaring_right'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
            }

            if ($this->smalotPdfHelper->textWithinDataTm($dataTm, 'WENSEN BIJ DEZE AANVRAAG')) {

                // wensen
                $coordsFromCompetenties = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Competenties', true);
                $textWithin = $page->getTextXY(132, $coordsFromCompetenties[1] - 40, 10, 40);
                $rawData['description_wensen_competenties'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromAk = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Aanvullende kennis', true);
                $textWithin = $page->getTextXY(132, $coordsFromAk[1] - 40, 10, 40);
                $rawData['description_wensen_aanvullende_kennis'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);

                $coordsFromWish3 = $this->smalotPdfHelper->getCoordsFromText($dataTm, 'Overige functiewensen', true);
                $textWithin = $page->getTextXY(132, $coordsFromWish3[1] - 40, 10, 40);
                $rawData['description_wensen_overige_functiewensen'] = $this->smalotPdfHelper->getAllTextFromDataTm($textWithin);
            }

        }

        $description = $this->smalotPdfHelper->getDescriptionHeader($rawData);

        $rawData['description'] = $description;

        // velden boven
        // Functie:  Servicedesk medewerker   (JA)
        // Locatie:  Zwolle  (city) JA
        // Startdatum: 18-10-2021   JA
        // Duur opdracht:  6 maanden    JA
        // Inzet per week: 40 uur    JA, Uren per week
        // Max. uurtarief: Marktconform  (vaste tekst)
        // Deadline aanbieden: 06-10-2021 vóór 12:00 uur  JA, indienen offertes (alleen dag)
        //
        // Achtergrond opdracht + Opdrachtbeschrijving
        //
        // eisen:
        // dominant kwaliteitenprofiel (2e kolom)
        // certificaten (2e kolom)
        // ervaring ( 1 + 2 concat)

        // wensen:
        // de 3 kolommen bij wensen

        // fixed data Dictu
        $rawData['description'] = $this->smalotPdfHelper->getDescriptionHeader($rawData);
        $rawData['company_id'] = 1193352;

        return array_merge($this->fixedData(), $rawData);
    }

}
