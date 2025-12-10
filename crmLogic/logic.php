<?php

require_once '../core/amo/AmoCrmClass.php';
require_once '../core/amo/AmoCrmController.php';

$timerStart = time();

if (
    !empty($_POST['contact_name'])
    && !empty($_POST['contact_email'])
    && !empty($_POST['contact_phone'])
    && !empty($_POST['lead_price'])
    && !empty($_POST['timer_start'])
) {
    $client = new AmoCrmClass();
    $timerEnd = time();

    $contactId = AmoCrmController::createAmoContact($client, $_POST);

    AmoCrmController::createAmoLead($client, $_POST, $contactId, $timerEnd);
}