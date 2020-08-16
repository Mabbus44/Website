<?php
	//Polls database for a specific move and returns it as soon as its available
	//Also checks if match ended, and if so returns results
	include_once(__DIR__."/../Functions/boardFunctions.php");
	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');
	if(DEBUG_INFO)
		er("getMove.php");

	$done = false;
	do{
		$move = getMove($_REQUEST["matchIndex"], $_REQUEST["moveIndex"]);
		if($move != -1){
			$done = true;
      echo "data: " . json_encode($move) . "\n\n";
			ob_flush();
			flush();
		}
		else{
			$result = getMatchResults($_REQUEST["matchIndex"]);
			if($result != -1){
				$done = true;
				echo "data: " . json_encode($result) . "\n\n";
				ob_flush();
				flush();
			}
		}
	}while(!$done);
?>