<?php

ini_set('memory_limit', '2048M');

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\ModelManager;



// Function to generate predictions for a specific site number and date
function generateMinMaxPredictions($specificSiteNumber, $specificDate)
{
    $locationNames = [
    1 => 'Wynyard',
    2 => 'Launceston',
    3 => 'Smithton',
    4 => 'Hobart',
    5 => 'Campania',
];
    $timestamp = strtotime($specificDate);
    $modelManager = new ModelManager();
    $linearHumidity = $modelManager->restoreFromFile('Saved_models/humidity_model.phpml');
    $linearTemperature = $modelManager->restoreFromFile('Saved_models/temperature_model.phpml');

    $predictedHumidity = $linearHumidity->predict([[$specificSiteNumber, $timestamp]]);
    $predictedTemperature = $linearTemperature->predict([[$specificSiteNumber, $timestamp]]);
    $temperatureRange = 9.5;
    $humidityRange = 25.5;

    $minTemperaturePrediction = $predictedTemperature[0] - ($temperatureRange / 2);
    $maxTemperaturePrediction = $predictedTemperature[0] + ($temperatureRange / 2);
    $minHumidityPrediction = $predictedHumidity[0] - ($humidityRange / 2);
    $maxHumidityPrediction = $predictedHumidity[0] + ($humidityRange / 2);

    return [
        'site_name' => $locationNames[$specificSiteNumber],
        'timestamp' => date('Y-m-d', $timestamp),
        'minTemperaturePrediction' => round($minTemperaturePrediction, 1),
        'maxTemperaturePrediction' => round($maxTemperaturePrediction, 1),
        'minHumidityPrediction' => round($minHumidityPrediction, 1),
        'maxHumidityPrediction' => round($maxHumidityPrediction, 1)
    ];
}


