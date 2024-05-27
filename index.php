<?php
include_once 'config.php';
require_once BASE_URL.'Clean_Train_and_predict/load_and_predict.php';

$raw_xml = simplexml_load_file("recordData.xml");
$json_data = json_encode($raw_xml);
$data = json_decode($json_data, true);
$last_record = end($data['record']);

$loadPredict = loadPredict($last_record['location_id'], $last_record['date']);


// Array mapping site numbers to location names
$locationNames = [
    91107 => 'Wynyard',
    91237 => 'Launceston',
    91292 => 'Smithton',
    94029 => 'Hobart',
    94212 => 'Campania',
    // Add more site numbers and names as needed
];

$locationName = isset($locationNames[$last_record['location_id']]) ? $locationNames[$last_record['location_id']] : 'Unknown Location';
$specificDate = $last_record['date'];
$dateMetrics = $loadPredict['dateMetrics'];
$halfHourAverages = $loadPredict['halfHourAverages'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Data Visualization</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .table {
            width: 100%;
        }

        .table th,
        .table td {
            text-align: center;
        }

        .table th {
            background-color: #f0f0f0;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

    </style>
</head>

<body>
<div class="container-fluid">
    <div class="row mt-4 justify-content-center">
        <div class="col text-center">
            <button class="btn btn-outline-success btn-lg active mr-2" id="temp-chart-btn" onclick="showTempChart()">
                Temperature
            </button>
            <button class="btn btn-outline-success btn-lg" id="humidity-chart-btn" onclick="showHumidityChart()">
                Humidity
            </button>
        </div>
    </div>
    <div class="row mt-4">
        <div id="temp-chart" class="chart-container mt-3">
            <div id="tempChart" style="height: 400px; width: 100%;"></div>
            <table class="table table-bordered mt-4">
                <thead>
                <tr>
                    <th>Temperature Metric</th>
                    <th>Predicted Value</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Predicted Min Temperature</td>
                    <td><b><?php echo($dateMetrics['minTemp'] !== null ? $dateMetrics['minTemp'] : 'N/A'); ?></b> &#176;
                    </td>
                </tr>
                <tr>
                    <td>Predicted Max Temperature</td>
                    <td><b><?php echo($dateMetrics['maxTemp'] !== null ? $dateMetrics['maxTemp'] : 'N/A'); ?> &#176;</b>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="humidity-chart" class="chart-container mt-3" style="display: none;">
            <div id="humidityChart" style="height: 400px; width: 100%;"></div>
            <table class="table table-bordered mt-4">
                <thead>
                <tr>
                    <th>Humidity Metric</th>
                    <th>Predicted Value</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Predicted Min Humidity</td>
                    <td><b><?php echo($dateMetrics['minHumid'] !== null ? $dateMetrics['minHumid'] : 'N/A'); ?></b></td>
                </tr>
                <tr>
                    <td>Predicted Max Humidity</td>
                    <td><b><?php echo($dateMetrics['maxHumid'] !== null ? $dateMetrics['maxHumid'] : 'N/A'); ?></b></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
    window.onload = function () {
        showTempChart(); // Render temperature chart when the page loads
    }

    function showTempChart() {
        $('#temp-chart').css('display', 'block');
        $('#humidity-chart').css('display', 'none');
        $('#temp-chart-btn').addClass('active');
        $('#humidity-chart-btn').removeClass('active');
        renderTempChart();
    }

    function showHumidityChart() {
        $('#temp-chart').css('display', 'none');
        $('#humidity-chart').css('display', 'block');
        $('#temp-chart-btn').removeClass('active');
        $('#humidity-chart-btn').addClass('active');
        renderHumidityChart();
    }

    const locationName = "<?php echo $locationName; ?>";
    const specificDate = '<?php echo $specificDate; ?>';

    function renderTempChart() {
        const tempChartData = <?php echo json_encode($halfHourAverages['averageTemps']); ?>;
        const tempDataPoints = tempChartData.map(data => ({
            x: new Date(`${specificDate} ${data.time}`),
            y: data.prediction_temp
        }));

        const tempChart = new CanvasJS.Chart("tempChart", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Predicted Temperature of " + locationName + " ( " + specificDate + " ) "
            },
            axisX: {
                title: "Time",
                valueFormatString: "HH:mm"
            },
            axisY: {
                title: "Temperature",
                includeZero: false
            },
            data: [{
                type: "scatter",
                name: "Temperature",
                toolTipContent: "<b>{x}</b><br>Temperature: {y}",
                dataPoints: tempDataPoints
            }]
        });
        tempChart.render();
    }

    function renderHumidityChart() {
        const humidityChartData = <?php echo json_encode($halfHourAverages['averageHumidity']); ?>;
        const humidityDataPoints = humidityChartData.map(data => ({
            x: new Date(`${specificDate} ${data.time}`),
            y: data.prediction_humidity
        }));

        const humidityChart = new CanvasJS.Chart("humidityChart", {
            animationEnabled: true,
            theme: "light2",
            title: {
                text: "Predicted Humidity of " + locationName + " ( " + specificDate + " ) "
            },
            axisX: {
                title: "Time",
                valueFormatString: "HH:mm"
            },
            axisY: {
                title: "Humidity",
                includeZero: false
            },
            data: [{
                type: "scatter",
                name: "Humidity",
                toolTipContent: "<b>{x}</b><br>Humidity: {y}",
                dataPoints: humidityDataPoints
            }]
        });
        humidityChart.render();
    }
</script>
</body>

</html>



