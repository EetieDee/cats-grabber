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

    public function getCatsoneSchaalId($schaal) {
        $arr = [8 => 173549, 9 => 173552, 10 => 173555, 11 => 173558, 12 => 173561, 13 => 173564, 14 => 173567, 15 => 173570];
        return $arr[$schaal];
    }
}
