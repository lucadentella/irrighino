# irrighino

irrighino is a complete watering system based on Arduino Yun
http://www.lucadentella.it/en/2015/08/04/irrighino/

# requirements

An Arduino Yun with the latest firmware and the following modules (you can install them using opkg or through the web interface):

* php5
* php5-cgi
* php5-cli
* php5-mod-curl
* php5-mod-json
* php5-mod-pdo
* php5-mod-pdo-sqlite
* zoneinfo-core
* zoneinfo-europe

The Yun webserver (uhttpd) must be configured to execute php scripts, as explained here:
http://www.lucadentella.it/en/2013/12/05/yun-utilizzare-php/


# installation

Upload the sketch to the Yun.
Copy all the files in the "website" folder to the Yun SD card, in a new folder named "irrighino".

Add the following 3 lines to the crontab ("crontab -e" or through the web interface):

>\* * * * * /usr/bin/php-cli /www/sd/irrighino/php/irrighinoTask.php

>05 00 * * * /usr/bin/php-cli /www/sd/irrighino/php/purgeOldEvents.php

>10 00 * * * /usr/bin/php-cli /www/sd/irrighino/php/purgeOldLogs.php

Create the log folder (/var/log/irrighino/)

If you changed the default Yun password ("arduino"), update the include.php file accordingly.


# usage

Connect to http://'yun-ip'/sd/irrighino


# customize

Edit the include.php file to change the number of outputs, their names and colors.
Edit the config.h file to change the PINs led, switches, outputs are connected to.
