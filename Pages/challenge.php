<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/challengeFunctions.php");
	checkIfLoggedIn();
	challengePlayer();
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<script type="text/javascript" src="../Js/challenge.js"></script>
		<title><?php dict("Go")?></title>
	</head>
	<body>
		<?php topPanel()?>
		<div class="contentDiv">
			<h1><?php dict("Challenge players")?></h1>
			<hr class="h1Line">
			<form action="../Pages/challenge.php" method="post">
				<div class="pairOfElements">
					<div class="firstOfPair"><input type="text" id="filterString" name="filterString"></div>
					<div class="secondOfPair"><button type="submit"><?php dict("Filter")?></button></div>
				</div>
			</form>
			<form action="../Pages/challenge.php" method="post">
				<div class="pairOfElements">
					<div class="firstOfPair"><?php
						listOfAllPlayers();
					?></div>
					<div class="secondOfPair"><button type="submit"><?php dict("Challenge")?></button></div>
				</div>
			</form>
			<hr>
			<form action="../Pages/challenge.php" method="post">
				<div class="pairOfElements">
					<div class="firstOfPair"><?php
						listOfChallengedPlayers();
					?></div>
					<div class="secondOfPair"><button type="submit"><?php dict("Remove challenge")?></button></div>
				</div>
			</form>
		</div>
	</body>
</html>