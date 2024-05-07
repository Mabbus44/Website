<?php
	include_once(__DIR__."/../Functions/boardFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Go</title>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="../Js/jQuery.js"></script>
		<script type="text/javascript" src="../Js/finishedGoBoard.js"></script>
	</head>
	<body>
		<div class="contentDiv">
			<h1 style="display:none"><?php dict("Board")?></h1>
			<hr class="h1Line" style="display:none">
			<div>
				<div id="canvasDiv">
					<canvas width=570 height=570 id="goCanvas" style="border:1px solid #000000;"></canvas>
				</div>
				<div class="bottomAlignedColumn">
					<button onclick="btnPreviewScore()">Preview score</button>
				</div>
			</div>
			<label style="clear:left; display:block;" id="dbg"></label>
			<script>
				document.getElementById("goCanvas").addEventListener("click", canvasClick, false);
				matchIndex = <?php echo $_REQUEST["id"]; ?>;
				yourTurnText = "Your turn";
				notYourTurnText = "Not your turn";
				selectLocationText = "Select location";
				areYouSureYouWantToGiveUpText = "Are you sure you want to give up?";
				loadBoard();
			</script>
		</div>
	</body>
</html>