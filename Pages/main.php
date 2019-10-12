<?php
	include_once("../Functions/accountFunctions.php");
	if(!checkIfLoggedIn()){
		header("Location: logIn.php");
	}
	include("../Functions/mainFunctions.php");
	acceptChallange();
?>
<!DOCTYPE html>
<html>
	<head>
		<script type="text/javascript" src="../Js/main.js"></script>
		<title>Go</title>
	</head>
	<body>
		<label><b>Main</b></label>
		<form action="../Pages/challange.php" method="post">
			<button type="submit">Challange players</button>
		</form>
		<form action="../Pages/main.php" method="post">
			<?php
				listOfChallanges();
			?>
			<button type="submit">Accept challange</button>
		</form>
		<form action="../Pages/profile.php" method="post">
			<button type="submit">Profile</button>
		</form>
		<form action="../Functions/logOut.php" method="post">
			<button type="submit">Log out</button>
		</form>
		<?php
			listOfMatches();
		?>
		<button type="button" id="goToGame" onclick="btnGoToGame()">Go to game</button>
</html>