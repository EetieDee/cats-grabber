<?php
namespace App\Transformers;

class CustomFieldTransformer
{
    public function transform($value)
    {
        $customFieldValue = new \stdClass();
        $customFieldValue->value = $value;
        return $value ? json_encode($customFieldValue): '';
    }


}


