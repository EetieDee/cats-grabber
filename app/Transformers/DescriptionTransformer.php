<?php
namespace App\Transformers;

use App\Helpers\ArrayHelper;

class DescriptionTransformer
{
    private $arrayHelper;

    public function __construct(ArrayHelper $arrayHelper)
    {
        $this->arrayHelper = $arrayHelper;
    }

    /*
     * Description:
     *  (header)
        Functie:  Servicedesk medewerker
        Locatie:  Zwolle  (city)
        Startdatum: 18-10-2021
        Duur opdracht:  6 maanden
        Inzet per week: 40 uur  (per week)
        Max. uurtarief: Marktconform  (vaste tekst)
        Deadline aanbieden: 06-10-2021 vóór 12:00 uur  JA, indienen offertes (alleen dag)

        Achtergrond opdracht + Opdrachtbeschrijving

        eisen:
        dominant kwaliteitenprofiel (2e kolom)
        certificaten (2e kolom)
        ervaring ( 1 + 2 concat)

        wensen:
        de 3 kolommen bij wensen
     */
    public function getDescription($descriptionToken): string
    {
        $description = '';
        $description .= $descriptionToken['header'];
        $description .= '<br /><br />';
        $description .= implode(',', $descriptionToken['aanvullend']);
        $description .= '<br /><br />';
        $description .= implode(',', $descriptionToken['toelichting']);
        $description .= '<br /><br />';
        $description .= 'Eisen:<br />';
        $description .= $this->arrayHelper->returnListFromArray($descriptionToken['eisen_dominant']);
        $description .= $this->arrayHelper->returnListFromArray($descriptionToken['eisen_overige_vereiste']);
        $description .= $this->arrayHelper->returnListFromArray($descriptionToken['ervaring']);
        $description .= '<br />';
        $description .= 'Wensen:<br />';
        $description .= $this->arrayHelper->returnListFromArray($descriptionToken['wensen_competenties']);
        $description .= $this->arrayHelper->returnListFromArray($descriptionToken['wensen_aanvullende_kennis']);
        $description .= $this->arrayHelper->returnListFromArray($descriptionToken['wensen_overige_functiewensen']);
        $description .= '<br />';

        return $description;
    }

    public function getHeader($rawData): string
    {
        $output = '';
        $output .= 'Functie: ' . $rawData['title'] . '<br />';
        $output .= 'Locatie: ' . $rawData['city'] . '<br />';
        $output .= 'Startdatum: ' . $rawData['start_date_header'] . '<br />';
        $output .= 'Duur opdracht: ' . $rawData['duration'] . '<br />';
        $output .= 'Inzet per week: ' . $rawData['hours_per_week'] . ' uur<br />';
        $output .= 'Max. uurtarief: Marktconform' . '<br />';
        $output .= 'Deadline aanbieden: ' . $rawData['deadline'] . '<br />';

        return $output;
    }


}
