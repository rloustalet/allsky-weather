<?php
include_once('includes/functions.php');
initialize_variables();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body { margin: 0; }
    </style>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Thomas Jacquin">
    <title>AllSky Public Page</title>

    <!-- Include the jQuery and Chart.js libraries -->
    <script src="documentation/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="js/chart.js"></script>

    <!-- Add your custom styles or include external stylesheets if needed -->

</head>
<body>

<div class="row">
    <div id="live_container" style="background-color: black;">
        <img id="current" class="current" src="<?php echo $image_name ?>" style="width:100%">
    </div>
</div>
<div class="row">
	<div class="col-md-6">
		<canvas id="temperatureChart" width="800" height="300"></canvas>
	</div>
	<div class="col-md-6">
		<canvas id="humidityChart" width="800" height="300"></canvas>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<canvas id="pressureChart" width="800" height="300"></canvas>
	</div>
	<div class="col-md-6">
		<canvas id="dewPointChart" width="800" height="300"></canvas>
	</div>
</div>

<script type="text/javascript">
    // Your existing JavaScript code for updating the live image
    function getImage() {
        var newImg = new Image();
        newImg.src = '<?php echo $image_name ?>?_ts=' + new Date().getTime();
        newImg.id = "current";
        newImg.class = "current";
        newImg.style = "width: 100%";

        newImg.decode().then(() => {
            $("#current")
                .attr('src', newImg.src)
                .attr("id", "current")
                .attr("class", "current")
                .css("width", "100%")
                .on('load', function () {
                    if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                        console.log('broken image!');
                    } else {
                        $("#live_container").empty().append(newImg);
                    }
                });
        }).finally(() => {
            // Use tail recursion to trigger the next invocation after `$delay` milliseconds
            setTimeout(getImage, <?php echo $delay ?>);
        });
    }

    // Your existing code to start updating the live image
    getImage();

    // Additional JavaScript code for weather chart functionality
    var temperatureChart, humidityChart, pressureChart, dewPointChart;
    var timestamps = []; // Initialize timestamps array

    function refreshWeatherData() {
        $.ajax({
            url: 'http://allsky.local:5000/weather_data',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data && !$.isEmptyObject(data)) {
                    data.timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    updateWeatherChart('temperatureChart', 'Temperature (°C)', 'rgba(255, 99, 132, 1)', data.temperature, data.timestamp);
                    updateWeatherChart('humidityChart', 'Humidity (%)', 'rgba(54, 162, 235, 1)', data.humidity, data.timestamp);
                    updateWeatherChart('pressureChart', 'Pressure (hPa)', 'rgba(255, 206, 86, 1)', data.pressure, data.timestamp);
                    updateWeatherChart('dewPointChart', 'Dew Point (°C)', 'rgba(75, 192, 192, 1)', data.dew_point, data.timestamp);

                    timestamps.push(data.timestamp);
                }

                setTimeout(refreshWeatherData, 300000);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching weather data:', error);
                setTimeout(refreshWeatherData, 300000);
            }
        });
    }

    function updateWeatherChart(chartId, label, borderColor, data, timestamp) {
        var chart = window[chartId];
        if (!chart) {
            initializeWeatherChart(chartId, label, borderColor, data, timestamp);
        } else {
            if (chart.data.labels.length >= 20) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
            }
            chart.data.labels.push(timestamp);
            chart.data.datasets[0].data.push(data);
            chart.update();
        }
    }

    function initializeWeatherChart(chartId, label, borderColor, initialData, timestamp) {
        var chartCtx = document.getElementById(chartId).getContext('2d');
        window[chartId] = new Chart(chartCtx, {
            type: 'line',
            data: {
                labels: [timestamp],
                datasets: [{
                    label: label,
                    data: [initialData],
                    borderColor: borderColor,
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    x: [{
                        type: 'category',
                        labels: timestamps,
                        position: 'bottom'
                    }],
                    y: [{
                        type: 'linear',
                        position: 'left'
                    }]
                }
            }
        });
    }

	refreshWeatherData();
</script>

</body>
</html>
