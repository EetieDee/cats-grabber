<?php
namespace App\Helpers;

class ArrayHelper
{
    public function returnListFromArray($tree): string
    {
        $output = '<ul>';
        foreach ( $tree as $item ) {
            if (trim($item)) {
                $output .= "<li> $item </li>";
            }
        }
        $output .= '</ul>';

        return $output;
    }

    public function concatTwoArrays($arr1, $arr2, $arr2PreText = ''): array
    {
        $output = [];
        foreach ($arr1 as $key => $val) {
            if (array_key_exists($key, $arr2)) {
                $output[] = $val .' - '.$arr2PreText.' '.$arr2[$key];
            } else {
                $output[] = $val;
            }
        }
        return $output;
    }
}
