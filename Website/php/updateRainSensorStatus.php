<?php

	require('include.php');

	logMessage("updateRainSensorStatus - started with argument $argv[1]");

	$db_handler  = DBconnect();
	
	if($argv[1]) logEvent($db_handler, TYPE_RAIN_ON, "Rain sensor activated");	
	else logEvent($db_handler, TYPE_RAIN_OFF, "Rain sensor deactivated");
	
	logMessage("updateRainSensorStatus - completed");
	echo "OK from updateRainSensorStatus.php";
?>