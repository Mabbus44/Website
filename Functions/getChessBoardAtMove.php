<?php
	//Polls database for new moves and returns them as soon as they are available
	include_once(__DIR__."/../Functions/chessBoardFunctions.php");
	header('Cache-Control: no-cache');
	session_start();
	if(DEBUG_INFO)
		er("getChessBoardAtMove.php");

	$result = array();
	$result["data"] = getChessBoardAtMove($_REQUEST["matchIndex"], $_REQUEST["moveIndex"]);
	echo json_encode($result);
?>