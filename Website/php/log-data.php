<?php

	require('include.php');

	class Response {
		
		public $draw;
		public $recordsTotal;
		public $recordsFiltered;
		public $data;
	}
	
	class Data {
		public $id;
		public $start;
		public $end;
		public $title;
		public $userId;
	}
	
	$response = new Response();
	$response->draw = $_POST["draw"];
	
	// get the search value
	$search_array = $_POST['search'];
	$search_value = $search_array['value'];
	
	// get the ordering parameters
	$ordering_array = $_POST['order'];
	$ordering_column = $ordering_array[0]['column'] + 1;
	$ordering_mode = $ordering_array[0]['dir'];
	
	// get the paging parameters
	$start = $_POST['start'];
	$length = $_POST['length'];	
	
	$db_handler  = DBconnect();

	// count the number of records in the DB
	$sql = "SELECT COUNT(ID) AS NUMRECORDS FROM LOG";
	if(!empty($search_value)) $sql .= " WHERE EVENT_DESC LIKE '%" . 	$search_value ."%'";
	
	foreach (DBquery($db_handler, $sql) as $row) {
		$response->recordsTotal = $row['NUMRECORDS'];
		$response->recordsFiltered = $row['NUMRECORDS'];
	}
	
	$sql = "SELECT EVENT_ID, datetime(DATE, 'localtime') as DATE, EVENT_DESC FROM LOG";
	if(!empty($search_value)) $sql .= " WHERE EVENT_DESC LIKE '%" . 	$search_value ."%'";
	$sql .= " ORDER BY " . $ordering_column . " " . $ordering_mode . " LIMIT " . $start . ", " . $length;
	
	$data = array();
	foreach (DBquery($db_handler, $sql) as $row) {
	
		$event = array();
		array_push($event, $row['EVENT_ID']);
		array_push($event, $row['DATE']);
		array_push($event, $row['EVENT_DESC']);
		array_push($data, $event);
	}
	
	$response->data = $data;
	$out = json_encode($response);
	header('Content-Type: application/json');
	echo $out;
?>