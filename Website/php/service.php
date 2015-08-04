<?php
	
	require('include.php');
	
	
	class Response {
	
		public $events;
	}
	
	class Event {
		public $id;
		public $start;
		public $end;
		public $title;
		public $userId;
	}
	
	class Output {
		public $id;
		public $status;
		public $managed_by;
	}
	
	class ReturnCode {
		
		public $code;
		public $message;
	}
	
	
	if(!isset($_GET["action"])) die("No action specified");
	if(empty($_GET["action"])) die("No action specified");
	
	$action = $_GET["action"];
	if($action == "get_events") {
		
		logMessage("IrrighinoService - get_events started");
		
		$db_handler  = DBconnect();
		
		$sql = "SELECT EVENT_ID, OUT_ID, START, END FROM EVENTS";
		$events = array();
		foreach (DBquery($db_handler, $sql) as $row) {
		
			$event = new Event();
			$event->id = intval($row['EVENT_ID']);
			$event->userId = intval($row['OUT_ID']);
			$event->start = strtotime($row['START']) * 1000;
			$event->end = strtotime($row['END']) * 1000;
			$event->title = "";
			array_push($events, $event);
		}
		$response = new Response();
		$response->events = $events;
		$out = json_encode($response);
		header('Content-Type: application/json');
		echo $out;
		
		logMessage("IrrighinoService - get_events completed");
	}
	
	else if($action == "insert_event") {
	
		logMessage("IrrighinoService - insert_event started");
	
		if(!isset($_GET["start"])) die("No start time specified");
		if(!isset($_GET["end"])) die("No end time specified");
		if(!isset($_GET["user_id"])) die("No user ID specified");

		$start_ts = $_GET["start"] / 1000;
		$end_ts = $_GET["end"] / 1000;
		$user_id = $_GET["user_id"];

		$start = date("Y-m-d H:i:s", $start_ts);
		$end = date("Y-m-d H:i:s", $end_ts);
		logMessage("IrrighinoService - Parameters: OUT_ID=$user_id START=$start END=$end");
		
		$db_handler  = DBconnect();

		$sql = "INSERT INTO EVENTS(OUT_ID, START, END) VALUES(" . $user_id . ",'" . $start . "','" . $end . "')";
		$count = DBexec($db_handler, $sql);
		$last_id = $db_handler->lastInsertId(); 
		
		$valve_id = "V" . ($user_id + 1);
		$start_time = date("H:i", $start_ts);
		$end_time = date("H:i", $end_ts);
		logEvent($db_handler, TYPE_CFG_CHANGE, "New ON period for output $valve_id: $start_time -> $end_time");
	
		$out = json_encode(array('event_id' => $last_id));
		header('Content-Type: application/json');
		echo $out;
		
		logMessage("IrrighinoService - insert_event completed, new event ID = $last_id");
	}

	else if($action == "update_event") {
	
		logMessage("IrrighinoService - update_event started");
	
		if(!isset($_GET["start"])) die("No start time specified");
		if(!isset($_GET["end"])) die("No end time specified");
		if(!isset($_GET["user_id"])) die("No user ID specified");
		if(!isset($_GET["event_id"])) die("No user ID specified");

		$event_id = $_GET["event_id"];
		$start_ts = $_GET["start"] / 1000;
		$end_ts = $_GET["end"] / 1000;
		$user_id = $_GET["user_id"];
		
		$start = date("Y-m-d H:i:s", $start_ts);
		$end = date("Y-m-d H:i:s", $end_ts);
		logMessage("IrrighinoService - Parameters: EVENT_ID=$event_id OUT_ID=$user_id START=$start END=$end");
		
		$db_handler  = DBconnect();

		$sql = "UPDATE EVENTS SET OUT_ID = " . $user_id . ", START = '" . $start . "', END = '" . $end . "' WHERE EVENT_ID = " . $event_id;
		$count = DBexec($db_handler, $sql);
		
		$valve_id = "V" . ($user_id + 1);
		$start_time = date("H:i", $start_ts);
		$end_time = date("H:i", $end_ts);
		logEvent($db_handler, TYPE_CFG_CHANGE, "Updated ON period for output $valve_id: $start_time -> $end_time");		
		
		$out = json_encode(array('num_updated' => $count));
		header('Content-Type: application/json');
		echo $out;
		
		logMessage("IrrighinoService - update_event completed, row updated = $count");
	}

	else if($action == "delete_event") {
	
		logMessage("IrrighinoService - delete_event started");
	
		if(!isset($_GET["event_id"])) die("No event ID specified");

		$event_id = $_GET["event_id"];
		logMessage("IrrighinoService - Parameters: EVENT_ID=$event_id");

		$db_handler  = DBconnect();		
		$sql = "SELECT EVENT_ID, OUT_ID, START, END FROM EVENTS WHERE EVENT_ID = " . $event_id;
		foreach (DBquery($db_handler, $sql) as $row) {
			$user_id = intval($row['OUT_ID']);
			$start_ts = strtotime($row['START']);
			$end_ts = strtotime($row['END']);
		}		

		$sql = "DELETE FROM EVENTS WHERE EVENT_ID = " . $event_id;
		$count = DBexec($db_handler, $sql);
	
		$valve_id = "V" . ($user_id + 1);
		$start_time = date("H:i", $start_ts);
		$end_time = date("H:i", $end_ts);
		logEvent($db_handler, TYPE_CFG_CHANGE, "Deleted ON period for output $valve_id: $start_time -> $end_time");		
		
		$out = json_encode(array('num_updated' => $count));
		header('Content-Type: application/json');
		echo $out;
		
		logMessage("IrrighinoService - delete_event completed, row deleted = $count");
	}
	
	else if($action == "get_out_status") {
		
		logMessage("IrrighinoService - get_out_status started");
		
		if(!isset($_GET["output_id"])) die("No output ID specified");
		$output_id = $_GET["output_id"];
		
		$db_handler  = DBconnect();
		
		$sql = "SELECT OUT_STATUS, MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $output_id";
		$output = new Output();
		foreach (DBquery($db_handler, $sql) as $row) {
			$output->id = $output_id;
			$output->status = intval($row['OUT_STATUS']);
			$output->managed_by = intval($row['MANAGED_BY']);
		}
		$out = json_encode($output);
		header('Content-Type: application/json');
		echo $out;
		
		logMessage("IrrighinoService - get_out_status completed");
	}	
	
	else if($action == "change_out") {
	
		logMessage("IrrighinoService - change_out started");
	
		if(!isset($_GET["output_id"]) || !isset($_GET["status_id"])) {
			
			sendReturnCode(-1, "No Output ID or Status ID specified");
			logMessage("IrrighinoService - Error, no OUT_ID or STATUS_ID specified");
		}

		$db_handler  = DBconnect();
		
		$output_id = $_GET["output_id"];
		$status_id = $_GET["status_id"];
		logMessage("IrrighinoService - Parameters: OUT_ID=$output_id OUT_STATUS=$status_id");
		
		// check if the output is not managed by physical switches
		$sql = "SELECT MANAGED_BY FROM OUTPUTS WHERE OUT_ID = $output_id";
		$data_set = DBquery($db_handler, $sql);
		if($data_set) {
		
			$row = $data_set->fetch();
			
			if($row) {
				
				$managed_by = intval($row['MANAGED_BY']);	
				
				if($managed_by != MANAGED_BY_SWITCH) {
					
					// new status OFF
					if($status_id == 0) {
						$response = callArduino("off/$$output_id");
						if(strpos($response, "OK") !== false) {
							
							// change the output status and managed by in the DB
							$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_OFF . " WHERE OUT_ID = $output_id";
							DBexec($db_handler, $sql);
							
							$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_WEB . " WHERE OUT_ID = $output_id";
							DBexec($db_handler, $sql);

							// log the event and return OK
							logEvent($db_handler, TYPE_OUT_OFF, "Output $output_id manually turned OFF via web");
							logMessage("IrrighinoService - OUT_ID $output_id turned OFF and event logged in the DB");
							sendReturnCode(0, "OK");							
						}
						else {
							logEvent($db_handler, TYPE_ERROR, "Unable to manually turn OFF output $output_id");
							logMessage("IrrighinoService - Error while calling Arduino to turn ON OUT_ID $output_id, response: $response");
							sendReturnCode(-1, "Unable to call Arduino to manually turn OFF output $output_id, error code: $response");					
						}
					}
					
					// new status ON
					else if($status_id == 2) {
						$response = callArduino("on/$$output_id");
						if(strpos($response, "OK") !== false) {
							
							// change the output status and managed by in the DB
							$sql = "UPDATE OUTPUTS SET OUT_STATUS = " . STATUS_ON . " WHERE OUT_ID = $output_id";
							DBexec($db_handler, $sql);
							
							$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_WEB . " WHERE OUT_ID = $output_id";
							DBexec($db_handler, $sql);

							// log the event and return OK
							logEvent($db_handler, TYPE_OUT_ON, "Output $output_id manually turned ON via web");
							logMessage("IrrighinoService - OUT_ID $output_id turned ON and event logged in the DB");
							sendReturnCode(0, "OK");							
						}
						else {
							logEvent($db_handler, TYPE_ERROR, "Unable to manually turn ON output $output_id");
							logMessage("IrrighinoService - Error while calling Arduino to turn ON OUT_ID $output_id, response: $response");
							sendReturnCode(-1, "Unable to call Arduino to manually turn ON output $output_id, error code: $response");					
						}

					}
					
					// new status AUTO, just update the database
					else if($status_id == 1) {						
						$sql = "UPDATE OUTPUTS SET MANAGED_BY = " . MANAGED_BY_AUTO . " WHERE OUT_ID = $output_id";
						DBexec($db_handler, $sql);

						// log the event and return OK
						logEvent($db_handler, TYPE_MANUAL_OVERRIDE, "Output $output_id configured in AUTO mode via web");
						logMessage("IrrighinoService - OUT_ID $output_id configured in AUTO mode and event logged in the DB");
						sendReturnCode(0, "OK");												
					}
				}
				
				// the output is controlled by a physical switch
				else {					
					logMessage("IrrighinoService - Unable to change status of OUT_ID $output_id because of it is controlled by a physical switch");
					sendReturnCode(-1, "Output $output_id is controlled by a physical switch");
				}
			}
			
			// no row was returned, missing information about that output!
			else {
				sendReturnCode(-1, "Output $output_id is not configured in SQLite");
				logMessage("IrrighinoService - Unable to change status of OUT_ID $output_id because it is not configured in SQLite");
			}
		}
		
		// an error has occurred while querying the DB
		// the query command returned false (= error)
		else {
			$error = $db_handler->errorInfo()[2];
			sendReturnCode(-1, "The SQLite DB returned the following error: $error");
			logMessage("IrrighinoService - The SQLite DB returned the following error: $error");
		}
		
		logMessage("IrrighinoService - change_out completed");
	}		
?>