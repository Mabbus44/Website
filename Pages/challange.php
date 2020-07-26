<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/challangeFunctions.php");
	checkIfLoggedIn();
	challangePlayer();
?>
<!DOCTYPE html>
<html>
	<head>
		<script type="text/javascript" src="../Js/challange.js"></script>
		<title>Go</title>
	</head>
	<body>
		<label><b>Challange players</b></label>
		<form action="../Pages/main.php" method="post">
			<button type="submit">Main</button>
		</form>
		<form action="../Pages/challange.php" method="post">
			<?php
				listOfAllPlayers();
			?>
			<button type="submit">Challange</button>
		</form>
		<form action="../Pages/challange.php" method="post">
			<?php
				listOfChallangedPlayers();
			?>
			<button type="submit">Remove challange</button>
		</form>
	</body>
</html>