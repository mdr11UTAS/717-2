<?php

ini_set('memory_limit', '2048M');
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'C:/xampp/vendor/autoload.php';

use Phpml\ModelManager;

// Function to calculate min and max temperature and humidity for a specific date and site number
function calculateMetrics($predictions, $siteNumber, $date)
{
    $minTemp = INF;
    $maxTemp = -INF;
    $minHumid = INF;
    $maxHumid = -INF;

    foreach ($predictions as $prediction) {
        if ($prediction['site_number'] == $siteNumber && date('Y-m-d', $prediction['timestamp']) === $date) {
            $minTemp = min($minTemp, $prediction['predictedTemperature']);
            $maxTemp = max($maxTemp, $prediction['predictedTemperature']);
            $minHumid = min($minHumid, $prediction['predictedHumidity']);
            $maxHumid = max($maxHumid, $prediction['predictedHumidity']);
        }
    }

    return [
        'minTemp' => floatval(number_format($minTemp, 8, '.', '')),
        'maxTemp' => floatval(number_format($maxTemp, 8, '.', '')),
        'minHumid' => floatval(number_format($minHumid, 8, '.', '')),
        'maxHumid' => floatval(number_format($maxHumid, 8, '.', '')),
    ];
}

// Function to calculate average temperature and humidity in half-hour increments
function calculateHalfHourAverages($predictions, $siteNumber, $date)
{
    $intervals = array_fill(0, 48, ['totalTemp' => 0.0, 'totalHumidity' => 0.0, 'count' => 0]);
    $timeIntervals = [];

    for ($i = 0; $i < 48; $i++) {
        $hour = str_pad(floor($i / 2), 2, '0', STR_PAD_LEFT);
        $minute = $i % 2 == 0 ? '00' : '30';
        $timeIntervals[] = "$hour:$minute";
    }

    foreach ($predictions as $prediction) {
        if ($prediction['site_number'] == $siteNumber && date('Y-m-d', $prediction['timestamp']) === $date) {
            $halfHourIndex = (int)(date('G', $prediction['timestamp']) * 2 + floor(date('i', $prediction['timestamp']) / 30));
            $intervals[$halfHourIndex]['totalTemp'] += $prediction['predictedTemperature'];
            $intervals[$halfHourIndex]['totalHumidity'] += $prediction['predictedHumidity'];
            $intervals[$halfHourIndex]['count']++;
        }
    }

    $averageTemps = [];
    $averageHumidity = [];

    foreach ($intervals as $index => $interval) {
        $time = $timeIntervals[$index];
        if ($interval['count'] > 0) {
            $averageTemps[] = ['time' => $time, 'prediction_temp' => floatval(number_format($interval['totalTemp'] / $interval['count'], 8, '.', ''))];
            $averageHumidity[] = ['time' => $time, 'prediction_humidity' => floatval(number_format($interval['totalHumidity'] / $interval['count'], 6, '.', ''))];
        } else {
            $averageTemps[] = ['time' => $time, 'prediction_temp' => null];
            $averageHumidity[] = ['time' => $time, 'prediction_humidity' => null];
        }
    }

    return ['averageTemps' => $averageTemps, 'averageHumidity' => $averageHumidity];
}

// Function to generate predictions for a specific site number and date
function generatePredictions($specificSiteNumber, $specificDate)
{
    $predictions = [];
    $timestamp = strtotime($specificDate . ' 00:00:00');
    // Load the trained models
    $modelManager = new ModelManager();
    $knnHumidity = $modelManager->restoreFromFile('Saved_models/humidity_model.phpml');
    $knnTemperature = $modelManager->restoreFromFile('Saved_models/temperature_model.phpml');

    for ($i = 0; $i < 48; $i++) {
        $predictedHumidity = $knnHumidity->predict([[$specificSiteNumber, $timestamp]]);
        $predictedTemperature = $knnTemperature->predict([[$specificSiteNumber, $timestamp]]);

        $predictions[] = [
            'site_number' => $specificSiteNumber,
            'timestamp' => $timestamp,
            'predictedHumidity' => $predictedHumidity[0],
            'predictedTemperature' => $predictedTemperature[0]
        ];


        // Increment timestamp by 30 minutes
        $timestamp += 1800;
    }

    return $predictions;
}

// Predict for a specific site number and date
$specificSiteNumber = 94212;
$specificDate = '2015-09-02';

// Function to display predictions for a specific site number and date
function loadPredict($specificSiteNumber, $specificDate)
{
    $loadPredict = [];
    $predictions = generatePredictions($specificSiteNumber, $specificDate);
    $loadPredict['dateMetrics'] = calculateMetrics($predictions, $specificSiteNumber, $specificDate);
    $loadPredict['halfHourAverages'] = calculateHalfHourAverages($predictions, $specificSiteNumber, $specificDate);

    return $loadPredict;

}




