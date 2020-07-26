<?php
	// Returns board with index "matchIndex"
	include_once(__DIR__."/../Functions/boardFunctions.php");
	$result = getBoard($_REQUEST["matchIndex"]);
	echo json_encode($result);
?>