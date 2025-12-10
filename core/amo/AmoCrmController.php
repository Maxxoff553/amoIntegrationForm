<?php

class AmoCrmController {
    const LEAD_NAME = 'Тестовый лид с контактом №';
    const CUSTOM_CHECKBOX_FIELD_ID = 2036741;
    const PHONE_FIELD_ID = 2028021;
    const EMAIL_FIELD_ID = 2028023;

    public static function createAmoContact(AmoCrmClass $client, array $formData): int {
        $contact = $client->apiPostRequest('contacts', [[
            'name' => $formData['contact_name'],
            'custom_fields_values' => [
                [
                    'field_id' => AmoCrmController::PHONE_FIELD_ID,
                    'values' => [
                        'value' => [
                            'value' => $formData['contact_phone']
                        ]
                    ]
                ],
                [
                    'field_id' => AmoCrmController::EMAIL_FIELD_ID,
                    'values' => [
                        'value' => [
                            'value' => $formData['contact_email']
                        ]
                    ]
                ]
            ]
        ]]);

        return $contact['_embedded']['contacts'][0]['id'];
    }

    public static function createAmoLead(AmoCrmClass $client, array $formData, int $contactId, int $timerEnd): void {
        $clientPageTime = $timerEnd - $formData['timer_start'];
        if ($clientPageTime > 30) {
            $checkBoxField = true;
        } else {
            $checkBoxField = false;
        }

        $client->apiPostRequest('leads', [[
            'name' => AmoCrmController::LEAD_NAME . $contactId,
            'price' => (int)$formData['lead_price'],
            '_embedded' => [
                'contacts' => [
                    [
                        'id' => $contactId
                    ]
                ]
            ],
            'custom_fields_values' => [
                [
                    'field_id' => AmoCrmController::CUSTOM_CHECKBOX_FIELD_ID,
                    'values' => [
                        'value' => [
                            'value' => $checkBoxField
                        ]
                    ]
                ]
            ]
        ]]);
    }
}