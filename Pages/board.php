<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
	include_once(__DIR__."/../Functions/boardFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="../Js/goBoard.js"></script>
		<title>Go</title>
	</head>
	<body>
		<canvas id="goCanvas" style="border:1px solid #000000;"></canvas>
		<button onclick="btnExecute()">Confirm!</button>
		<button onclick="btnPass()">Pass</button>
		<button onclick="btnGiveUp()">Give up</button>
		<label id="yourTurn"></label>
		<label id="dbg"></label>
		<script>
			document.getElementById("goCanvas").addEventListener("click", canvasClick, false);
			matchIndex = <?php echo $_REQUEST["id"]; ?>;
			playerColor = <?php echo getPlayerColor($_REQUEST["id"]); ?>;
			loadBoard();
		</script>
	</body>
</html>