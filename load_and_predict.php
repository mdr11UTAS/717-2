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
        'minTemp' => round($minTemp, 8),
        'maxTemp' => round($maxTemp, 8),
        'minHumid' => round($minHumid, 8),
        'maxHumid' => round($maxHumid, 8),
    ];
}

// Function to generate predictions for a specific site number and date
function generatePredictions($specificSiteNumber, $specificDate)
{
    $predictions = [];
    $timestamp = strtotime($specificDate . ' 00:00:00');
    // Load the trained models
    $modelManager = new ModelManager();
    $linearHumidity = $modelManager->restoreFromFile('Saved_models/humidity_model.phpml');
    $linearTemperature = $modelManager->restoreFromFile('Saved_models/temperature_model.phpml');

    for ($i = 0; $i < 48; $i++) {
        $predictedHumidity = $linearHumidity->predict([[$specificSiteNumber, $timestamp]]);
        $predictedTemperature = $linearTemperature->predict([[$specificSiteNumber, $timestamp]]);
        $hour = str_pad(floor($i / 2), 2, '0', STR_PAD_LEFT);
        $minute = $i % 2 == 0 ? '00' : '30';

        $predictions[] = [
            'site_number' => $specificSiteNumber,
            'timestamp' => $timestamp,
            'halfHourTime' => "$hour:$minute",
            'predictedHumidity' => $predictedHumidity[0],
            'predictedTemperature' => $predictedTemperature[0]
        ];


        // Increment timestamp by 30 minutes
        $timestamp += 1800;
    }

    return $predictions;
}

// Function to display predictions for a specific site number and date
function loadPredict($specificSiteNumber, $specificDate)
{
    $loadPredict = [];
    $loadPredict['halfHourAverages'] = generatePredictions($specificSiteNumber, $specificDate);
    $loadPredict['dateMetrics'] = calculateMetrics($loadPredict['halfHourAverages'], $specificSiteNumber, $specificDate);
    return $loadPredict;
}

$xmlFilePath = "recordData.xml";
$raw_xml = simplexml_load_file($xmlFilePath);
$json_data = json_encode($raw_xml);
$data = json_decode($json_data, true);
$last_record = end($data['record']);


$locationNames = [
    1 => 'Wynyard',
    2 => 'Launceston',
    3 => 'Smithton',
    4 => 'Hobart',
    5 => 'Campania',
];

