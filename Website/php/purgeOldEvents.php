<?php

	require('include.php');

	logMessage("purgeOldEvents - started");

	$db_handler  = DBconnect();
	$sql = "DELETE FROM LOG WHERE DATE <= date('now','-" . RETENTION_DAYS . " day')";
	$count = DBexec($db_handler, $sql);
	
	logMessage("purgeOldEvents - completed, deleted $count old events");
?>