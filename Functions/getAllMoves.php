<?php
	// Returns board with index "matchIndex"
	include_once(__DIR__."/../Functions/boardFunctions.php");
	if(DEBUG_INFO)
		er("getAllMoves.php");
	$result = getAllMoves($_REQUEST["matchIndex"]);
	echo json_encode($result);
?>