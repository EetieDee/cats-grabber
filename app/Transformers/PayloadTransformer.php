<?php
namespace App\Transformers;

class PayloadTransformer
{
    public function transform($rawData)
    {
        $isHot = false;
        $contactId = 216;

        return [
            'title' => $rawData['title'],
            'location' => [
                'city' => $rawData['city'],
                'state' => $rawData['state'],
                'postal_code' => $rawData['postal_code'],
            ],
            'country_code' => $rawData['country_code'],
            'company_id' => config('UKOMST_COMPANY_ID'),
            'category_name' => $rawData['category_name'],
            'is_hot' => $isHot,
            'start_date' => $rawData['start_date'],
            'salary' => $rawData['salary'],
            'max_rate' => $rawData['max_rate'],
            'duration' => $rawData['duration'],
            'type' => $rawData['type'],
            'openings' => $rawData['openings'],
            'description' => $rawData['description'],
            'notes' => $rawData['notes'],
            'contact_id' => $contactId,
            'custom_fields' => [
                'zwembad' => true,
                'sauna' => true
            ]
        ];
    }
}
