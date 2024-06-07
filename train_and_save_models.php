<?php

ini_set('memory_limit', '2048M');
// Set error logging to a file
ini_set('error_log', '/path/to/php_errors.log');

// Enable error reporting and display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use Phpml\Regression\LeastSquares;
use Phpml\ModelManager;

// Path to the CSV file
$csvFilePath = 'Training_data/cleaned_training_data.csv';

// Open the CSV file for reading
$file = fopen($csvFilePath, 'r');

// Skip the header row
$header = fgetcsv($file);

// Initialize arrays to store data
$samples = [];
$targetsMaxHumidity = [];
$targetsMinHumidity = [];
$targetsMaxTemperature = [];
$targetsMinTemperature = [];

// Initialize arrays to store data
$dates = [];
$maxHumidity = [];
$minHumidity = [];
$maxTemperature = [];
$minTemperature = [];

// Read each line of the CSV file
while (($row = fgetcsv($file)) !== false) {
    // Extract data from the row
    $date = date('Y-m-d', strtotime($row[1]));
    $humidity = (float)$row[2];
    $temperature = (float)$row[3];
    $site_id = $row[0];

    // Check if date already exists in the array
    if (!isset($dates[$date])) {
        // If not, initialize values for the date
        $dates[$date] = true;
    }

    // Store or update maximum and minimum humidity and temperature for each site ID and date
    if (!isset($maxHumidity[$date][$site_id])) {
        $maxHumidity[$date][$site_id] = $humidity;
        $minHumidity[$date][$site_id] = $humidity;
        $maxTemperature[$date][$site_id] = $temperature;
        $minTemperature[$date][$site_id] = $temperature;
    } else {
        $maxHumidity[$date][$site_id] = max($maxHumidity[$date][$site_id], $humidity);
        $minHumidity[$date][$site_id] = min($minHumidity[$date][$site_id], $humidity);
        $maxTemperature[$date][$site_id] = max($maxTemperature[$date][$site_id], $temperature);
        $minTemperature[$date][$site_id] = min($minTemperature[$date][$site_id], $temperature);
    }

    // Store data for regression
    $samples[] = [$site_id, strtotime($row[1])]; // Assuming the timestamp is in the second column
    $targetsMaxHumidity[] = $maxHumidity[$date][$site_id];
    $targetsMinHumidity[] = $minHumidity[$date][$site_id];
    $targetsMaxTemperature[] = $maxTemperature[$date][$site_id];
    $targetsMinTemperature[] = $minTemperature[$date][$site_id];
}

// Close the CSV file
fclose($file);

// Train four separate models: maximum and minimum humidity, maximum and minimum temperature

// Initialize LeastSquares for maximum humidity regression
$maxLinearHumidity = new LeastSquares();
$maxLinearHumidity->train($samples, $targetsMaxHumidity);

// Initialize LeastSquares for minimum humidity regression
$minLinearHumidity = new LeastSquares();
$minLinearHumidity->train($samples, $targetsMinHumidity);

// Initialize LeastSquares for maximum temperature regression
$maxLinearTemperature = new LeastSquares();
$maxLinearTemperature->train($samples, $targetsMaxTemperature);

// Initialize LeastSquares for minimum temperature regression
$minLinearTemperature = new LeastSquares();
$minLinearTemperature->train($samples, $targetsMinTemperature);

// Save the trained models
$modelManager = new ModelManager();
$modelManager->saveToFile($maxLinearHumidity, 'Saved_models/max_humidity_model.phpml');
$modelManager->saveToFile($minLinearHumidity, 'Saved_models/min_humidity_model.phpml');
$modelManager->saveToFile($maxLinearTemperature, 'Saved_models/max_temperature_model.phpml');
$modelManager->saveToFile($minLinearTemperature, 'Saved_models/min_temperature_model.phpml');

echo "Models trained and saved successfully.\n";
