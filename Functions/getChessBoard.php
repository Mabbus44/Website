<?php
	//Polls database for new moves and returns them as soon as they are available
	include_once(__DIR__."/../Functions/chessBoardFunctions.php");
	header('Cache-Control: no-cache');
	session_start();
	if(DEBUG_INFO)
		er("getChessBoard.php");

	$result = array();
	$result["data"] = getChessBoard($_REQUEST["matchIndex"]);
	echo json_encode($result);
?>