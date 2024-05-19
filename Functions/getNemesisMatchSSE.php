<?php
	//Polls database for new moves and returns them as soon as they are available
	include_once(__DIR__."/../Functions/chessBoardFunctions.php");
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');
	session_start();
	if(DEBUG_INFO)
		er("getNemesisMatchSSE.php");

	$done = false;
	$startTime = time();
	do{
		$result = getChessNemesisMatchIdAndStatus();
		if(!$result["matchOver"])
			$done = true;
		if($done){
			echo "data: " . json_encode($result) . "\n\n";
			ob_flush();
			flush();
		}
		if(time() - $startTime > 5){
			$done = true;
		}
		if(!$done){
			sleep(2);
		}
	}while(!$done);
	echo "event: restart\n";
	echo "data: {}\n\n";
	exit();
?>