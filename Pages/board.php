<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
	include_once(__DIR__."/../Functions/boardFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<script type="text/javascript" src="../Js/goBoard.js"></script>
		<title><?php dict("Go")?></title>
	</head>
	<body>
		<?php topPanel()?>
		<div class="contentDiv">
			<h1><?php dict("Board")?></h1>
			<hr class="h1Line">
			<div>
				<div id="canvasDiv">
					<canvas width=570 height=570 id="goCanvas" style="border:1px solid #000000;"></canvas>
					<div id="canvasMessageDiv">
						<label id="canvasLabel"></label>
						<div id="canvasButtonsDiv">
							<button onclick="btnYes()" id="canvasYesButton">Yes</button>
							<button onclick="btnNo()" id="canvasNoButton">No</button>
							<button onclick="btnOk()" id="canvasOkButton">Ok</button>
						</div>
					</div>
				</div>
				<div class="bottomAlignedColumn">
					<label id="yourTurn"></label>
					<button onclick="btnGiveUp()"><?php dict("Give up")?></button>
					<button onclick="btnPass()"><?php dict("Pass")?></button>
					<button onclick="btnExecute()"><?php dict("Confirm")?></button>
				</div>
			</div>
			<label style="clear:left; display:block;" id="dbg"></label>
			<script>
				document.getElementById("goCanvas").addEventListener("click", canvasClick, false);
				matchIndex = <?php echo $_REQUEST["id"]; ?>;
				playerColor = <?php echo getPlayerColor($_REQUEST["id"]); ?>;
				yourTurnText = "<?php dict("Your turn")?>";
				notYourTurnText = "<?php dict("Not your turn")?>";
				loadBoard();
			</script>
		</div>
	</body>
</html>