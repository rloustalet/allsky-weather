<?php

function DisplayWeather() {
    $status = new StatusMessages();

    ?>
    <script src="documentation/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="js/chart.js"></script>
    <script>

        

        var temperatureChart, humidityChart, pressureChart, dewPointChart;
        var allData = [];
        var timestamps = [];

        function refreshWeatherData() {
            $.ajax({
                url: 'http://allsky.local:5000/weather_data',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data && !$.isEmptyObject(data)) {
                        // Enregistrez l'heure d'origine des données
                        data.timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                        updateWeatherChart('temperatureChart', 'Temperature (°C)', 'rgba(255, 99, 132, 1)', data.temperature, data.timestamp);
                        updateWeatherChart('humidityChart', 'Humidity (%)', 'rgba(54, 162, 235, 1)', data.humidity, data.timestamp);
                        updateWeatherChart('pressureChart', 'Pressure (hPa)', 'rgba(255, 206, 86, 1)', data.pressure, data.timestamp);
                        updateWeatherChart('dewPointChart', 'Dew Point (°C)', 'rgba(75, 192, 192, 1)', data.dew_point, data.timestamp);

                        updateWeatherDataTable(data);

                        allData.push(data);
                        timestamps.push(data.timestamp);
                        setCookie('allWeatherData', JSON.stringify(allData), 1); // 1 day expiration
                        setCookie('timestamps', JSON.stringify(timestamps), 1); // 1 day expiration
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
                chart.data.labels.push(timestamp); // Utiliser l'heure d'origine
                chart.data.datasets[0].data.push(data);
                chart.update();
            }
        }

        function initializeWeatherChart(chartId, label, borderColor, initialData, timestamp) {
            var chartCtx = document.getElementById(chartId).getContext('2d');
            window[chartId] = new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: [timestamp], // Utiliser l'heure d'origine
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
                            type: 'category', // Utiliser l'échelle de catégorie pour les horodatages
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


        function updateWeatherDataTable(data) {
            var html = '<h2>Last Weather Data</h2>';
            html += '<table border="1">';
            html += '<tr><th>Measurement</th><th>Value</th></tr>';
            html += '<tr><td>Temperature (°C)</td><td>' + data.temperature + '</td></tr>';
            html += '<tr><td>Humidity (%)</td><td>' + data.humidity + '</td></tr>';
            html += '<tr><td>Pressure (hPa)</td><td>' + data.pressure + '</td></tr>';
            html += '<tr><td>Dew Point (°C)</td><td>' + data.dew_point + '</td></tr>';
            html += '</table>';
            $('#weatherDataTable').html(html);
        }

        function setCookie(name, value, days) {
            document.cookie = name + '=' + encodeURIComponent(value) + '; path=/';
        }

        function getCookie(name) {
            var nameEQ = name + '=';
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
            return null;
        }

        $(document).ready(function () {
            loadInitialWeatherData();
        });

        function loadInitialWeatherData() {
            var storedData = getCookie('allWeatherData');
            var storedTimestamps = getCookie('timestamps');
            if (storedData && storedTimestamps) {
                allData = JSON.parse(storedData);
                timestamps = JSON.parse(storedTimestamps);
                for (var i = 0; i < allData.length; i++) {
                    updateWeatherChart('temperatureChart', 'Temperature (°C)', 'rgba(255, 99, 132, 1)', allData[i].temperature, timestamps[i]);
                    updateWeatherChart('humidityChart', 'Humidity (%)', 'rgba(54, 162, 235, 1)', allData[i].humidity, timestamps[i]);
                    updateWeatherChart('pressureChart', 'Pressure (hPa)', 'rgba(255, 206, 86, 1)', allData[i].pressure, timestamps[i]);
                    updateWeatherChart('dewPointChart', 'Dew Point (°C)', 'rgba(75, 192, 192, 1)', allData[i].dew_point, timestamps[i]);
                }

                updateWeatherDataTable(allData[allData.length - 1]);
            }
            setTimeout(refreshWeatherData, 100);
        }
    </script>

    <div class="row">
        <div class="panel panel-primary">
            <div class="panel-heading"><i class="fa fa-cloud-moon"></i> Weather Information</div>
            <div class="panel-body">
                <div id="weather_info_container" class="cursorPointer weather_info_container" title="Click to view details">
                    <!-- Your existing HTML code for weather information display -->
                </div>
                <div id="weatherDataTable"></div>
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
            </div>
        </div>
    </div>

    <?php 
}
?>
