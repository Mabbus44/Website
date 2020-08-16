<?php
	//Polls database for a specific result and returns it as soon as its available
	include_once(__DIR__."/../Functions/boardFunctions.php");
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');
	if(DEBUG_INFO)
		er("getMatchResult.php");

	$done = false;
	do{
		$result = getMatchResults($_REQUEST["matchIndex"]);
		if($result != -1){
			$done = true;
      echo "data: " . json_encode($result) . "\n\n";
			ob_flush();
			flush();
		}
	}while(!$done);
?>