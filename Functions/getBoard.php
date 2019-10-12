<?php
	include "boardFunctions.php";
	$result = getBoard($_REQUEST["matchIndex"]);
	echo json_encode($result);
?>