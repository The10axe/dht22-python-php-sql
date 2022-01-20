import time
import board
import adafruit_dht
import psutil

import mysql.connector
from mysql.connector import Error

delay = 60
errorn = 0

# We first check if a libgpiod process is running. If yes, we kill it!
for proc in psutil.process_iter():
    if proc.name() == 'libgpiod_pulsein' or proc.name() == 'libgpiod_pulsei':
        proc.kill()

sensor = adafruit_dht.DHT22(board.D23)


def create_connection(host_name, user_name, user_password):
    connection = None
    try:
        connection = mysql.connector.connect(
            host=host_name,
            user=user_name,
            passwd=user_password
        )
        print("Connection to MySQL DB successful")
    except Error as e:
        print(f"The error '{e}' occurred")

    return connection


def execute_query(connection, query):
    cursor = connection.cursor()
    try:
        cursor.execute(query)
        connection.commit()
        print("Query executed successfully")
    except Error as e:
        print(f"The error '{e}' occurred")


connection = None

while connection == None:
    connection = create_connection("localhost", "username", "password")


while True:
    try:
        temp = sensor.temperature
        humidity = sensor.humidity
    except RuntimeError as error:
        #print(error.args[0])
        #time.sleep(2.0)
        errorn = errorn + 1
        continue
    except Exception as error:
        sensor.exit()
        raise error
    
    if (delay >= 60) and (temp != None):
        print("Temperature: {}°C   Humidity: {}%   Error: {}".format(temp, humidity, errorn))
        sql = "INSERT INTO `sensor`.`bedroom` (`temperature`, `humidite`) VALUES ('"+str(temp)+"', '1"+str(humidity)+"')"
        execute_query(connection, sql)
        delay = 0
        errorn = 0

    time.sleep(2.0)
    delay = delay + 2