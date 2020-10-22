<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
	include_once(__DIR__."/../Functions/mainFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<script type="text/javascript" src="../Js/main.js"></script>
		<title><?php dict("Go")?></title>
	</head>
	<body>
		<?php topPanel()?>
		<div class="contentDiv">
			<h1><?php dict("Main")?></h1>
			<hr class="h1Line">
			<form action="../Pages/challenge.php">
				<button type="submit"><?php dict("Challenge players")?></button>
			</form>
			<form action="../Pages/choseColor.php" method="post">
				<div class="pairOfElements">
					<div class="firstOfPair"><?php
						listOfChallenges();
					?></div>
					<div class="secondOfPair"><button type="submit"><?php dict("Accept challenge")?></button></div>
				</div>
			</form>
			<div class="pairOfElements">
				<div class="firstOfPair"><?php
					listOfMatches();
				?></div>
				<div class="secondOfPair"><button type="button" id="goToGame" onclick="btnGoToGame()"><?php dict("Go to game")?></button></div>
			</div>
			<a href="../changelog.txt" target="_blank" id="changelogLink">v 1.02</a>
		</div>
	</body>
</html>