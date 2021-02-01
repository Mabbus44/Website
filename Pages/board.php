<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
	include_once(__DIR__."/../Functions/boardFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php dict("Go")?></title>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<script type="text/javascript" src="../Js/goBoard.js"></script>
	</head>
	<body>
		<?php topPanel()?>
		<div class="contentDiv">
			<h1 style="display:none"><?php dict("Board")?></h1>
			<hr class="h1Line" style="display:none">
			<div>
				<div id="canvasDiv">
					<canvas width=570 height=570 id="goCanvas" style="border:1px solid #000000;"></canvas>
					<div id="canvasMessageDiv">
						<label id="canvasLabel"></label>
						<div id="canvasButtonsDiv">
							<button onclick="btnYes()" id="canvasYesButton"><?php dict("Yes")?></button>
							<button onclick="btnNo()" id="canvasNoButton"><?php dict("No")?></button>
							<button onclick="btnOk()" id="canvasOkButton"><?php dict("Ok")?></button>
						</div>
					</div>
				</div>
				<div class="bottomAlignedColumn">
					<div id="yourTurnDiv">
						<label id="yourTurn"></label>
					</div>
					<div class="leftAlignedRow">
						<canvas width=30 height=30 id="blackStone"></canvas>
						<label id="blackName"><?php echo getNameFromMatchID($_REQUEST["id"], 0); ?></label>
						<img id="blackArrow" src="../Graphics/arrow40x30.png" alt="arrow">
						<label id="blackScore"></label>
					</div>
					<div class="leftAlignedRow">
						<canvas width=30 height=30 id="whiteStone"></canvas>
						<label id="whiteName"><?php echo getNameFromMatchID($_REQUEST["id"], 1); ?></label>
						<img id="whiteArrow" src="../Graphics/arrow40x30.png" alt="arrow">
						<label id="whiteScore"></label>
					</div>
					<button onclick="btnPreviewScore()"><?php dict("Preview score")?></button>
					<button onclick="btnGiveUp()" id="btnGiveUp"><?php dict("Give up")?></button>
					<button onclick="btnPass()" id="btnPass"><?php dict("Pass")?></button>
					<button onclick="btnExecute()" id="btnExecute"><?php dict("Confirm")?></button>
				</div>
			</div>
			<label style="clear:left; display:block;" id="dbg"></label>
			<script>
				document.getElementById("goCanvas").addEventListener("click", canvasClick, false);
				matchIndex = <?php echo $_REQUEST["id"]; ?>;
				playerColor = <?php echo getPlayerColor($_REQUEST["id"]); ?>;
				if(playerColor == 2){
					document.getElementById("btnGiveUp").style.display = "none";
					document.getElementById("btnPass").style.display = "none";
					document.getElementById("btnExecute").style.display = "none";
				}
				yourTurnText = "<?php dict("Your turn")?>";
				notYourTurnText = "<?php dict("Not your turn")?>";
				selectLocationText = "<?php dict("Select location")?>";
				areYouSureYouWantToGiveUpText = "<?php dict("Are you sure you want to give up?")?>";
				var blackStoneCanvas = document.getElementById("blackStone");
				var bctx = blackStoneCanvas.getContext("2d");
				bctx.fillStyle = "#000000";
				bctx.beginPath();
				bctx.arc(0.5*squareSize, 0.5*squareSize, squareSize*0.4, 0, 2 * Math.PI);
				bctx.fill();
				var whiteStoneCanvas = document.getElementById("whiteStone");
				var wctx = whiteStoneCanvas.getContext("2d");
				wctx.fillStyle = "#FFFFFF";
				wctx.beginPath();
				wctx.arc(0.5*squareSize, 0.5*squareSize, squareSize*0.4, 0, 2 * Math.PI);
				wctx.fill();
				wctx.strokeStyle = "#000000";
				wctx.beginPath();
				wctx.arc(0.5*squareSize, 0.5*squareSize, squareSize*0.4, 0, 2 * Math.PI);
				wctx.stroke();
				loadBoard();
			</script>
		</div>
	</body>
</html>