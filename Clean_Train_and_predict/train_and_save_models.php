<?php

ini_set('memory_limit', '2048M');
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'C:/xampp/vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;

// Path to the CSV file
$csvFilePath = BASE_URL.'Training_data/cleaned_training_data.csv';

// Open the CSV file for reading
$file = fopen($csvFilePath, 'r');

// Skip the header row
$header = fgetcsv($file);

// Initialize arrays to store data
$samples = [];
$targetsHumidity = [];
$targetsTemperature = [];

// Read each line of the CSV file
while (($row = fgetcsv($file)) !== false) {
    // Extract data from the row
    $site_number = (int)$row[0];
    $timestamp = (int)strtotime($row[1]);
    $humidity = (float)$row[2];
    $temperature = (float)$row[3];

    // Store data in arrays
    $samples[] = [$site_number, $timestamp];
    $targetsHumidity[] = $humidity;
    $targetsTemperature[] = $temperature;
}

// Close the CSV file
fclose($file);

// Initialize KNearestNeighbors for humidity regression
$knnHumidity = new KNearestNeighbors();
$knnHumidity->train($samples, $targetsHumidity);


// Initialize KNearestNeighbors for temperature regression
$knnTemperature = new KNearestNeighbors();
$knnTemperature->train($samples, $targetsTemperature);

// Save the trained models
$modelManager = new ModelManager();
$modelManager->saveToFile($knnHumidity, 'Saved_models/humidity_model.phpml');
$modelManager->saveToFile($knnTemperature, 'Saved_models/temperature_model.phpml');

echo "Models trained and saved successfully.\n";
