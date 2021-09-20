<?php
namespace App\Transformers;

class PayloadTransformer
{
    public function transform($rawData)
    {


        $locationObj = new \stdClass();
        $locationAttributes = [
            'city',
            'state',
            'postal_code'
        ];
        foreach($locationAttributes as $attribute) {
            $locationObj = $this->setAttributeWhenSet($locationObj, $attribute, $rawData[$attribute]);
        }

        $payloadObj = new \stdClass();
        $payloadAttributes = [
            'title',
            'company_id',
            'department_id',
            'recruiter_id',
            'owner_id',
            'category_name',
            'is_hot',
            'start_date',
            'salary',
            'max_rate',
            'duration',
            'type',
            'openings',
            'external_id',
            'description',
            'notes',
            'country_code',
            'contact_id',
            'workflow_id'
        ];
        foreach($payloadAttributes as $attribute) {
            $payloadObj = $this->setAttributeWhenSet($payloadObj, $attribute, $rawData[$attribute]);
        }

        $payloadObj->location = $locationObj;
        // todo custom_fields

        return json_encode($payloadObj);
    }

    private function setAttributeWhenSet($payloadObj, $attribute, $value) {
        if ($value) {
            $payloadObj->$attribute = $value;
        }

        return $payloadObj;

    }
}
