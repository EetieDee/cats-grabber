<?php
namespace App\Services;

use phpDocumentor\Reflection\Types\Integer;
use Smalot\PdfParser\Page;

class SmalotPdfHelper
{
    public static $TYPE_IVO30 = 'ivo30';
    public static $TYPE_IVO37 = 'ivo37';
    public static $TYPE_DICTU = 'dictu';
    public static $TYPE_LOGIUS = 'logius';

    private $dateHelper;

    public function __construct(DateHelper $dateHelper)
    {
        $this->dateHelper = $dateHelper;
    }

    public function getPageByText($pdf, $text)
    {
        $pages  = $pdf->getPages();

        foreach ($pages as $page) {
            $dataTm = $page->getDataTm();
            foreach ($dataTm as $data) {
                if ($data[1] === $text) {
                    return $page;
                }
            }
        }

        return null;
    }

    public function getTextByPos($dataTm, $pos)
    {
        if (!array_key_exists($pos, $dataTm)) {
            return null;
        }

        return $dataTm[$pos][1];
    }

    public function getAllTextFromDataTm($dataTm)
    {
        $text = [];
        foreach($dataTm as $currentTm) {
            $text[] = $currentTm[1];
        }

        return $text;
    }

    public function getCoordsFromText($dataTm, $text, $strict = false)
    {
        if (!$dataTm) {
            return null;
        }

        $selectedDataTmElem = array_filter($dataTm, function ($elem) use ($text, $strict) {
            if ($strict) {
                return $elem[1] == $text;
            } else {
                return strpos($elem[1], $text) !== false;
            }
        });

        if (count($selectedDataTmElem) === 0) {
            return;
        }

        $firstSelectedDataTmElem = array_values($selectedDataTmElem)[0];

        return [
            $firstSelectedDataTmElem[0][4], $firstSelectedDataTmElem[0][5]
        ];
    }


    public function getTextFromX($page)
    {
        echo '<pre>'; print_r($page->getTextXY(132, 820, 10, 60)); exit;
    }

    public function getDescription($rawData): string
    {
        // todo here
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


        return $this->getDescriptionHeader($rawData);
    }

    public function getDescriptionHeader($rawData): string
    {
        $header = '';
        $header .= 'Functie: ' . $rawData['title'] . '<br />';
        $header .= 'Locatie: ' . $rawData['city'] . '<br />';
        $header .= 'Startdatum: ' . $this->dateHelper->formatDutchdate($rawData['dutch_date'], 'd-m-Y') . '<br />';
        $header .= 'Duur opdracht: ' . $rawData['duration'] . '<br />';
        $header .= 'Inzet per week: ' . $rawData['hours_per_week'] . ' uur<br />';
        $header .= 'Max. uurtarief: Marktconform' . '<br />';
        $header .= 'Deadline aanbieden: ' . $this->dateHelper->formatDutchDate($rawData['deadline'], 'd-m-Y') . '<br />';

        return $header;
    }


    public function assumeType(Page $page)
    {
        // assume IVO30: it is type 'ivo' when the following coords consists of version 3.0
        //   850, 1180
        //   820, 1180
        $dataTmsIvo1 = $page->getTextXY(850, 1180, 25, 25);
        $dataTmsIvo2 = $page->getTextXY(820, 1180, 25, 25);
        if ($this->textWithinDataTm($dataTmsIvo1, '3.0') || $this->textWithinDataTm($dataTmsIvo2, '3.0')) {
            return self::$TYPE_IVO30;
        }

        // assume IVO37: it is type 'ivo' when the following coords consists of version 3.7
        //   850, 1180
        //   820, 1180
        if ($this->textWithinDataTm($dataTmsIvo1, '3.7') || $this->textWithinDataTm($dataTmsIvo2, '3.7')) {
            return self::$TYPE_IVO37;
        }

        // assume DICTU: it is type 'dictu' when the following coords consists of version 5.4
        //   850, 1273
        //   530, 789
        $dataTmsDictu1 = $page->getTextXY(850, 1273, 25, 25);
        $dataTmsDictu2 = $page->getTextXY(530, 789, 25, 25);
        if ($this->textWithinDataTm($dataTmsDictu1, '5.4') || $this->textWithinDataTm($dataTmsDictu2, '5.4')) {
            return self::$TYPE_DICTU;
        }

        // assume LOGIUS: it is type 'logius' when the following coords consists of version 5.1
        //   852, 1273
        //   529, 789
        $dataTmsLogius1 = $page->getTextXY(851, 1273, 15, 15);
        $dataTmsLogius2 = $page->getTextXY(529, 789, 15, 15);
        if ($this->textWithinDataTm($dataTmsLogius1, '5.1') || $this->textWithinDataTm($dataTmsLogius2, '5.1')) {
            return self::$TYPE_LOGIUS;
        }

    }

    public function textWithinDataTm($dataTm, $text, $strict = false): bool
    {
        $selectedDataTmElem = array_filter($dataTm, function ($elem) use ($text, $strict) {

            if ($strict) {
                return $elem[1] == $text;
            } else {
                return strpos($elem[1], $text) !== false;
            }
        });

        return count($selectedDataTmElem) > 0;
    }


}
