from flask import Flask, jsonify
from flask_cors import CORS
import smbus
import bme680
import math  # Ajout de l'import math

app = Flask(__name__)
app.json.sort_keys = False
CORS(app)

# I2C address of the BME680 sensor
BME680_I2C_ADDR = 0x77

# Configuration of the BME680 sensor
bus = smbus.SMBus(1)  # Use I2C bus 1 on newer Raspberry Pi models, 0 on older ones
bme = bme680.BME680(i2c_addr=BME680_I2C_ADDR, i2c_device=bus)

def get_weather_data():
    if bme.get_sensor_data():
        temperature = round(bme.data.temperature, 2)
        humidity = round(bme.data.humidity, 0)
        pressure = round(bme.data.pressure, 0)
        dew_point = round(calculate_dew_point(temperature, humidity), 2)

        return {
            "temperature": temperature,
            "humidity": humidity,
            "pressure": pressure,
            "dew_point": dew_point
        }
    else:
        return {"error": "Failed to read sensor data"}

def calculate_dew_point(temperature, humidity):
    a = 17.27
    b = 237.7
    alpha = ((a * temperature) / (b + temperature)) + math.log(humidity / 100.0)
    dew_point = (b * alpha) / (a - alpha)
    return dew_point

@app.route('/weather_data')
def weather_data():
    data = get_weather_data()
    return jsonify(data)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
