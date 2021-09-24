<?php
namespace App\Services;

class DateHelper
{
    /**
     * @param $dutchDate maandag 13 september 2021
     * return 2021-09-13
     */
    public function formatDutchDate($dutchDate, $format = 'd-m-Y')
    {
        $df = \IntlDateFormatter::create(
            'nl_NL',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'America/Chicago',
            \IntlDateFormatter::GREGORIAN,
            'eeee d MMMM yyyy'
        );
        return date($format, $df->parse($dutchDate));
    }
}
