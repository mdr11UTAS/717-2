<?php
require_once 'config.php';
require_once BASE_URL . 'load_and_predict.php';

function calculateHalfHourlyAverages($inputDate, $siteId) {
    $csvFilePath = BASE_URL . 'Training_data/cleaned_training_data.csv';
    // Parse input date to extract day and month
    $inputDayMonth = date("d-m", strtotime($inputDate));

    // Initialize an array to store temperatures for each half-hour interval
    $data = array_fill(0, 48, []); // 48 half-hour intervals in a day

    if (($handle = fopen($csvFilePath, 'r')) !== false) {
        // Skip header row
        fgetcsv($handle);

        // Iterate through the dataset
        while (($row = fgetcsv($handle)) !== false) {
            $currentSiteId = (int)$row[0];
            $date = date("d-m", strtotime($row[1]));
            $temperature = (float)$row[3];
            $humidity = (float)$row[2];

            // Check if the record matches the input date and site ID
            if ($date === $inputDayMonth && $currentSiteId === $siteId) {
                $time = date("H:i", strtotime($row[1])); // Extract time (HH:mm)
                $halfHourIndex = floor((strtotime($time) - strtotime("00:00")) / (30 * 60)); // Calculate half-hour index

                $data[$halfHourIndex][] = [
                    'siteId' => $siteId,
                    'temperature' => $temperature,
                    'humidity' => $humidity
                ];
            }
        }

        fclose($handle);
    }

    // Initialize arrays to store the total temperature, humidity, and count for each half-hour interval
    $halfHourlyAverages = array_fill(0, 48, ['totalTemperature' => 0, 'totalHumidity' => 0, 'count' => 0]);

    foreach ($data as $intervalIndex => $intervalData) {
        foreach ($intervalData as $record) {
            // Check if the record matches the input site ID
            if ($record['siteId'] == $siteId) {
                $halfHourlyAverages[$intervalIndex]['totalTemperature'] += $record['temperature'];
                $halfHourlyAverages[$intervalIndex]['totalHumidity'] += $record['humidity'];
                $halfHourlyAverages[$intervalIndex]['count']++;
            }
        }
    }

    $averageData = [];

    // Calculate the average temperature and humidity for each half-hour interval
    foreach ($halfHourlyAverages as $intervalIndex => $totals) {
        $averageTemperature = $totals['count'] > 0 ? $totals['totalTemperature'] / $totals['count'] : 0;
        $averageHumidity = $totals['count'] > 0 ? $totals['totalHumidity'] / $totals['count'] : 0;

        $interval = date("H:i", strtotime("00:00") + ($intervalIndex * 30 * 60)); // Calculate start time of the interval

        $averageData[] = [
            'date' => $inputDate,
            'time' => $interval,
            'avgTemp' => round($averageTemperature,1),
            'avgHumid' => round($averageHumidity,1)
        ];
    }

    return $averageData;
}



