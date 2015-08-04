<?php

	require('include.php');
	
	$min_timestamp = strtotime("-" . RETENTION_DAYS . " day");
	$count = 0;
	logMessage("purgeOldLogs - started with  min timestamp = $min_timestamp");

	$log_files = array_diff(scandir(LOG_DIR), array('..', '.'));
	foreach($log_files as $log_file) {
		$last_modified_timestamp = filemtime(LOG_DIR . $log_file);
		if($last_modified_timestamp < $min_timestamp) {
			unlink(LOG_DIR . $log_file);
			$count++;
		}
	}
	
	logMessage("purgeOldLogs - completed, deleted $count old log files");
?>