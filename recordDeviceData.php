<?php
require_once 'config.php';
require_once BASE_URL.'Clean_Train_and_predict/load_and_predict.php';

// Show any errors
error_reporting(E_ALL);
ini_set('display_errors', '1');


$directory = 'C:/xampp/htdocs/717/';

// Ensure the directory exists and set its permissions
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}
chmod($directory, 0777);


$locationNames = [
    91107 => 'Wynyard',
    91237 => 'Launceston',
    91292 => 'Smithton',
    94029 => 'Hobart',
    94212 => 'Campania',
    // Add more site numbers and names as needed
];

// Check if all necessary parameters are present
if (
    isset($_GET['location_id']) &&
    isset($_GET['date']) && isset($locationNames[$_GET['location_id']])
) {
    // Initialize an empty string
    $str = '';

    // If the XML file already exists, read its content
    if (file_exists('recordData.xml')) {
        $str = file_get_contents('recordData.xml');
    }

    // If the string is empty, initialize it with XML declaration
    if (strlen($str) == 0) {
        $str = "<?xml version='1.0' encoding='UTF-8'?>\n<records></records>";
    }

    // Create new XML data for appending
    $newData = "\n<record>
        <location_id>{$_GET['location_id']}</location_id>
        <location_name>{$locationNames[$_GET['location_id']]}</location_name>
        <date>{$_GET['date']}</date>
    </record>\n</records>";

    // Put the new data at the end of the XML document
    $str = str_replace("</records>", $newData, $str);

    // Write the updated XML back to the server
    $saveFile = file_put_contents('recordData.xml', $str);

    // Check if file writing was successful and set HTTP response code accordingly
    if (!$saveFile) {
        http_response_code(405); // Method Not Allowed
    }


    $loadPredict = loadPredict($_GET['location_id'], $_GET['date']);

    // Send the data as a JSON response
    header('Content-Type: application/json');
    $response = [
        'date' => $_GET['date'],
        'location_id' => $_GET['location_id'],
        'location_name' => $locationNames[$_GET['location_id']],
        'metrics' => $loadPredict['dateMetrics']
    ];
    echo json_encode($response);
} else {
    echo json_encode(array("error" => "Location id not found"));
}
?>
