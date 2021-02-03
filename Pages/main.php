<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
	include_once(__DIR__."/../Functions/mainFunctions.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="../Js/jQuery.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<script type="text/javascript" src="../Js/main.js"></script>
		<title><?php dict("Go")?></title>
	</head>
	<body>
		<?php topPanel()?>
		<script>
			language = <?php echo getSession("language");?>
		</script>
		<div class="contentDiv">
			<h1><?php dict("Main")?></h1>
			<hr class="h1Line">
			<button type="button" id="openRules" onclick="btnOpenRules()"><?php dict("Rules")?></button>
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
			<a href="../changelog.txt" target="_blank" id="changelogLink">v 1.04</a>
		</div>
	</body>
</html>