<?php

// --------------- Database & constants ---------------

define ("DB_PATH", "sqlite:../db/irrighino.db");

define ("TYPE_OUT_ON", 0);
define ("TYPE_OUT_OFF", 1);
define ("TYPE_RAIN_ON", 2);
define ("TYPE_RAIN_OFF", 3);
define ("TYPE_CFG_CHANGE", 4);
define ("TYPE_MANUAL_OVERRIDE", 5);
define ("TYPE_ERROR", 6);

define ("MANAGED_BY_SWITCH", 0);
define ("MANAGED_BY_WEB", 1);
define ("MANAGED_BY_AUTO", 2);

define ("STATUS_OFF", 0);
define ("STATUS_ON", 1);


// --------------- Arduino configuration ---------------

define ("ARDUINO_USERNAME", "root");
define ("ARDUINO_PASSWORD", "arduino");
define ("ARDUINO_BASE_URL", "http://localhost/arduino/");


// --------------- Output configuration ---------------

define ("OUTPUTS_NUMBER", 3);

$outputs = array();
$outputs[0]["name"] = "Front    ";
$outputs[0]["baseColor"] = "#FFD800";
$outputs[0]["borderColor"] = "#E5BF00";

$outputs[1]["name"] = "Left";
$outputs[1]["baseColor"] = "#FF0000";
$outputs[1]["borderColor"] = "#E00000";

$outputs[2]["name"] = "Right";
$outputs[2]["baseColor"] = "#00FF21";
$outputs[2]["borderColor"] = "#00E21A";


// --------------- Timestamp and logging configuration ---------------

date_default_timezone_set("Europe/Rome");
define("LOG_DIR", "/var/log/irrighino/");
define("RETENTION_DAYS", 7);


// --------------- Arduino communication functions ---------------
function callArduino($command) {
	
	$fullURL = ARDUINO_BASE_URL . $command;
	
	// inizialize the cURL library
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_USERPWD, ARDUINO_USERNAME . ":" . ARDUINO_PASSWORD);
	
	// set gateway URL and parameters
	curl_setopt($ch,CURLOPT_URL, $fullURL);
	
	// make the request and return the response (or the error message if failed)
	$response = curl_exec($ch);
	if($response == false) $response = curl_error($ch);
	
	curl_close($ch);
	return $response;
}


// --------------- Events and return codes functions ---------------
function logEvent($db_handler, $eventType, $message) {
	
	$sql = "INSERT INTO LOG(EVENT_ID, DATE, EVENT_DESC) VALUES($eventType, DATETIME('NOW'), '$message')";
	DBexec($db_handler, $sql);
}

function sendReturnCode($code, $message) {
	
	$returnCode = new ReturnCode();
	$returnCode->code = $code;
	$returnCode->message = $message;
	
	$out = json_encode($returnCode);
	header('Content-Type: application/json');
	echo $out;
}

function logMessage($msg) {
	
		chdir (__DIR__);
		$logfile_suffix = date("Ymd");
		$current_time = date("Y-m-d H:i:s");
		$msg = "$current_time - $msg";
		$log_file_descriptor = fopen(LOG_DIR . "irrighino.log." . $logfile_suffix, "a");
		fwrite($log_file_descriptor, $msg . PHP_EOL);
		fclose($log_file_descriptor);
}


// --------------- Database functions ---------------

// Connect to the database and return the handler (or false if error)
function DBconnect() {
	
	try {
		$db_handler = new PDO(DB_PATH);
	} catch (PDOException $e) {
		echo $e;
		$db_handler = false;
	}
	return $db_handler;
}

// Close the connection
function DBdisconnect() {
	
	$db_handler = null;
}

// Execute a query and return a dataset
function DBquery($handler, $sql) {
	
	if($handler == false) return false;
	return $handler->query($sql);
}

// Execute a query and return the number of affected rows
function DBexec($handler, $sql) {
	
	if($handler == false) return false;
	return $handler->exec($sql);
}
?>
