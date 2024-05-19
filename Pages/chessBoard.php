<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
	include_once(__DIR__."/../Functions/chessBoardFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php dict("Chess")?></title>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script>
			playerId = <?php echo (issetSession("id") ? getSession("id") : -1);?>;
			matchId = <?php echo (isset($_REQUEST["id"]) ? $_REQUEST["id"]: 0);?>;
			yourTurnText = "<?php dict("Your turn")?>";
			notYourTurnText = "<?php dict("Not your turn")?>";
			checkText = "<?php dict("Check")?>";
			checkMateText = "<?php dict("Check mate")?>";
			drawText = "<?php dict("Draw")?>";
			someoneWonText = "<?php dict("Someone won")?>";
			drawResultText = "<?php dict("Drawresult")?>";
			newGameText = "<?php dict("Start new game")?>";
			startingNewGameText = "<?php dict("Starting new game")?>";
		</script>
		<script type="text/javascript" src="../Js/jQuery.js"></script>
		<script type="text/javascript" src="../Js/semaphore.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<script type="text/javascript" src="../Js/chessBoard.js"></script>
	</head>
	<body>
		<?php chessTopPanel()?>
		<div class="contentDiv">
			<div>
				<div id="canvasDiv">
					<canvas width=570 height=570 id="chessBoardCanvas" style="border:1px solid #000000;"></canvas>
					<div id="chessCanvasMessageDiv">
						<label id="canvasLabel"></label>
						<div id="chessCanvasButtonsDiv">
							<button onclick="startNewGame('white')" id="canvasWhiteButton">
								<img src = "../Graphics/Chess/VB.png" width=30 height=30></img>
								<?php dict("White")?>
							</button>
							<button onclick="startNewGame('black')" id="canvasBlackButton">
								<img src = "../Graphics/Chess/SB.png" width=30 height=30></img>
								<?php dict("Black")?>
							</button>
						</div>
					</div>
				</div>
				<div class="chessBottomAlignedColumn" id="namesColumn">
					<div id="checkDiv">
						<label id="checkLabel"></label>
					</div>
					<div id="yourTurnDiv">
						<label id="yourTurn"></label>
					</div>
					<div class="leftAlignedRow">
						<img src = "../Graphics/Chess/VB.png" width=30 height=30 id="whitePawn"></img>
						<label id="whiteName">Player 1</label>
						<img id="whiteArrow" src="../Graphics/arrow40x30.png" alt="arrow">
					</div>
					<div class="leftAlignedRow">
						<img src = "../Graphics/Chess/SB.png" width=30 height=30 id="blackPawn"></img>
						<label id="blackName">Player 2</label>
						<img id="blackArrow" src="../Graphics/arrow40x30.png" alt="arrow">
					</div>
				</div>
			</div>
		</div>
	</body>
	<script>domLoaded();</script>
</html>