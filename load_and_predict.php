<?php

ini_set('memory_limit', '2048M');

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\ModelManager;
use Phpml\Regression\LeastSquares;

// Function to generate predictions for a specific site number and date
function generateMinMaxPredictions($specificSiteNumber, $specificDate)
{
    // Mapping of site numbers to location names
    $locationNames = [
        1 => 'Wynyard',
        2 => 'Launceston',
        3 => 'Smithton',
        4 => 'Hobart',
        5 => 'Campania',
    ];

    // Load the trained models
    $modelManager = new ModelManager();
    $maxHumidityModel = $modelManager->restoreFromFile('Saved_models/max_humidity_model.phpml');
    $minHumidityModel = $modelManager->restoreFromFile('Saved_models/min_humidity_model.phpml');
    $maxTemperatureModel = $modelManager->restoreFromFile('Saved_models/max_temperature_model.phpml');
    $minTemperatureModel = $modelManager->restoreFromFile('Saved_models/min_temperature_model.phpml');

    // Convert the specific date to a timestamp
    $timestamp = strtotime($specificDate);

    // Predict maximum and minimum humidity and temperature
    $maxHumidityPrediction = $maxHumidityModel->predict([[$specificSiteNumber, $timestamp]]);
    $minHumidityPrediction = $minHumidityModel->predict([[$specificSiteNumber, $timestamp]]);
    $maxTemperaturePrediction = $maxTemperatureModel->predict([[$specificSiteNumber, $timestamp]]);
    $minTemperaturePrediction = $minTemperatureModel->predict([[$specificSiteNumber, $timestamp]]);

    // Format the predictions and return
    return [
        'site_name' => $locationNames[$specificSiteNumber],
        'timestamp' => date('Y-m-d', $timestamp),
        'minTemperaturePrediction' => round($minTemperaturePrediction[0], 1),
        'maxTemperaturePrediction' => round($maxTemperaturePrediction[0], 1),
        'minHumidityPrediction' => round($minHumidityPrediction[0], 1),
        'maxHumidityPrediction' => round($maxHumidityPrediction[0], 1)
    ];
}
