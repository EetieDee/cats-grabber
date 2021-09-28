<?php
namespace App\Services;

use App\Helpers\DateHelper;
use Smalot\PdfParser\Page;

class SmalotPdfHelper
{
    public static $TYPE_IVO30 = 'ivo30';
    public static $TYPE_IVO37 = 'ivo37';
    public static $TYPE_DICTU530 = 'dictu530';
    public static $TYPE_DICTU850 = 'dictu850';
    public static $TYPE_LOGIUS530 = 'logius530';
    public static $TYPE_LOGIUS850 = 'logius850';

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

    public function getAllTextFromDataTm($dataTm, $str = false)
    {
        $text = [];
        foreach($dataTm as $currentTm) {
            $text[] = $currentTm[1];
        }

        if ($str && count($text) > 0) {
            return $text[0];
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

        // assume DICTU850: it is type 'dictu' when the following coords consists of version 5.4
        //   850, 1273
        $dataTmsDictu1 = $page->getTextXY(850, 1273, 25, 25);
        if ($this->textWithinDataTm($dataTmsDictu1, '5.4')) {
            return self::$TYPE_DICTU850;
        }

        // assume DICTU530: it is type 'dictu' when the following coords consists of version 5.4
        //   530, 789
        $dataTmsDictu2 = $page->getTextXY(530, 789, 25, 25);
        if ($this->textWithinDataTm($dataTmsDictu2, '5.4')) {
            return self::$TYPE_DICTU530;
        }

        // assume LOGIUS850: it is type 'logius' when the following coords consists of version 5.1
        //   850, 1273
        $dataTmsLogius1 = $page->getTextXY(850, 1273, 15, 15);
        if ($this->textWithinDataTm($dataTmsLogius1, '5.1')) {
            return self::$TYPE_LOGIUS850;
        }

        // assume LOGIUS530: it is type 'logius' when the following coords consists of version 5.1
        //   530, 789
        $dataTmsLogius2 = $page->getTextXY(530, 789, 15, 15);
        if ($this->textWithinDataTm($dataTmsLogius2, '5.1')) {
            return self::$TYPE_LOGIUS530;
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
