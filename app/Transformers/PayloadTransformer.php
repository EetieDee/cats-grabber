<?php
namespace App\Transformers;

class PayloadTransformer
{
    public function transform($rawData)
    {
        $attributesToCast = [
            'openings' => 'integer'
        ];

        $payloadObj = new \stdClass();
        $payloadAttributes = [
            'title',
            'company_id',
            'department_id',
            'recruiter_id',
            'owner_id',
            'category_name',
            'is_hot',
            'salary',
            'max_rate',
            'duration',
            'type',
            'openings',
            'external_id',
            'description',
            'start_date',
            'notes',
            'country_code',
            'contact_id',
            'workflow_id'
        ];

        foreach($payloadAttributes as $attribute) {
            $payloadObj = $this->setAttributeWhenSet($payloadObj, $attribute, $rawData[$attribute], $attributesToCast);
        }

        // add location
        $locationObj = new \stdClass();
        $locationAttributes = [
            'city',
            'state',
            'postal_code'
        ];
        foreach($locationAttributes as $attribute) {
            $locationObj = $this->setAttributeWhenSet($locationObj, $attribute, $rawData[$attribute], $attributesToCast);
        }
        $payloadObj->location = $locationObj;

        return json_encode($payloadObj);
    }

    private function setAttributeWhenSet($payloadObj, $attribute, $value, $attributesToCast) {
        if ($value) {
            if (array_key_exists($attribute, $attributesToCast)) {
                $action = $attributesToCast[$attribute];

                switch($action) {
                    case 'integer':
                        $payloadObj->$attribute = (int)$value;
                        break;
                    default:
                        $payloadObj->$attribute = $value;
                }
            } else {
                $payloadObj->$attribute = $value;
            }
        }

        return $payloadObj;

    }
}
