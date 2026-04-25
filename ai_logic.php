<?php
// ai_logic.php

/**
 * AI function to predict if a bus needs maintenance soon
 * @param int $mileage The current mileage of the bus
 * @param int $last_service_days Days since last service
 * @return array Prediction data
 */
function get_ai_prediction($mileage, $last_service_days) {
    // Basic AI "Score" Logic
    // In real AI, this would be a machine learning model
    $risk_score = ($mileage * 0.0001) + ($last_service_days * 0.5);
    
    if ($risk_score > 80) {
        return [
            'status' => 'Critical',
            'color' => 'red',
            'insight' => 'High risk of engine failure. Schedule service immediately.'
        ];
    } elseif ($risk_score > 50) {
        return [
            'status' => 'Warning',
            'color' => 'orange',
            'insight' => 'Wear detected. Maintenance recommended within 7 days.'
        ];
    } else {
        return [
            'status' => 'Optimal',
            'color' => 'green',
            'insight' => 'Vehicle performing efficiently.'
        ];
    }
}
?>