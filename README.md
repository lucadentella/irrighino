# irrighino

irrighino is a complete watering system based on Arduino Yun
http://www.lucadentella.it/en/2015/08/04/irrighino/

# requirements

An Arduino Yun with the latest firmware and the following modules (you can install them using opkg):
- php5
- php5-cgi
- php5-cli

# installation

Upload the sketch to the Yun.
Copy all the files in the "website" folder to the Yun SD card, in a new folder named "irrighino".

# usage

Connect to http://<yun-ip>/sd/irrighino

# customize

Edit the include.php file to change the number of outputs, their names and colors.
Edit the config.h file to change the PINs led, switches, outputs are connected to.
