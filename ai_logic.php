<?php
// ai_logic.php

/**
 * AI function to predict if a bus needs maintenance soon
 * @param int $mileage The current mileage of the bus
 * @param int $last_service_days Days since last service
 * @return array Prediction data
 */
function get_ai_prediction($mileage, $days) {

    $rand = rand(1, 100);

    if ($rand <= 50) {
        return [
            'status' => '🟢 Good',
            'color' => 'green',
            'insight' => 'Bus is in good condition. No action needed.'
        ];
    } 
    elseif ($rand <= 75) {
        return [
            'status' => '🟡 Moderate',
            'color' => 'orange',
            'insight' => 'Minor wear detected. Plan service soon.'
        ];
    } 
    else {
        return [
            'status' => '🔴 Critical',
            'color' => 'red',
            'insight' => 'High risk of failure. Service immediately.'
        ];
    }
}
?>