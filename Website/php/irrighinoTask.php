<?php

	require('include.php');
	logMessage("IrrighinoTask - Started");
	
	$db_handler  = DBconnect();

	// convert the actual date to the calendar one (2000-01-02 -> 2000-01-08, Sunday to Saturday)
	$day_of_week = date("w");
	$day = $day_of_week + 2;
	$actual_time = date("H:i:s");
	$actual_date = "2000-01-0$day $actual_time";
	
	// retrieve the outputs to be managed by this task
	$sql = "SELECT OUT_ID, OUT_STATUS FROM OUTPUTS WHERE MANAGED_BY = " . MANAGED_BY_AUTO;
	$data_set = DBquery($db_handler, $sql);
	
	if($data_set) {
		
		foreach($data_set as $row) {

			$out_id = intval($row['OUT_ID']);
			$out_status = intval($row['OUT_STATUS']);
			logMessage("IrrighinoTask - Checking OUT_ID $out_id now in OUT_STATUS $out_status");	
			
			// check if we neeed to change the status
			$sql = "SELECT EVENT_ID FROM EVENTS WHERE OUT_ID = $out_id AND START < '$actual_date' AND END > '$actual_date'";
			$event_data_set = DBquery($db_handler, $sql);
			
			// a event has been found
			if($event_data_set->fetch()) {
				
				logMessage("IrrighinoTask - An event was found for this OUT_ID");

				// if the output is OFF, turn it ON
				if($out_status == STATUS_OFF) {

					$response = callArduino("on/$out_id");
					if(strpos($response, "OK") !== false) {
						$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_ON . " WHERE OUT_ID = $out_id";
						DBexec($db_handler, $sql);
						logEvent($db_handler, TYPE_OUT_ON, "Output $out_id turned ON by a schedule");
						logMessage("IrrighinoTask - OUT_ID $out_id turned ON and event logged in the DB");
					}
					
					// an error has occurred while calling Arduino
					else {
						logEvent($db_handler, TYPE_ERROR, "Unable to turn ON output $out_id");
						logMessage("IrrighinoTask - Error while calling Arduino to turn ON OUT_ID $out_id, response: $response");
					}
				}
			}
			
			// no events found
			else {
				
				logMessage("IrrighinoTask - No events found for this OUT_ID");
				
				// if the output is ON, turn it OFF
				if($out_status == STATUS_ON) {

					$response = callArduino("off/$out_id");
					if(strpos($response, "OK") !== false) {
						$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_OFF . " WHERE OUT_ID = $out_id";
						DBexec($db_handler, $sql);					
						logEvent($db_handler, TYPE_OUT_OFF, "Output $out_id turned OFF by a schedule");
						logMessage("IrrighinoTask - OUT_ID $out_id turned OFF and event logged in the DB");
					}
					
					// an error has occurred while calling Arduino
					else {
						logEvent($db_handler, TYPE_ERROR, "Unable to turn OFF output $out_id");
						logMessage("IrrighinoTask - Error while calling Arduino to turn ON OUT_ID $out_id, response: $response");
					}
				}
			}
			
		}
	
	// the query command returned false (= error)
	} else {
		logMessage("IrrighinoTask - Error while querying the DB: " . $db_handler->errorInfo()[2]);
	}
	
	DBdisconnect();
	
	logMessage("IrrighinoTask - Completed");
?>