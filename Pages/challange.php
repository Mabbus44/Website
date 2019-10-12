<?php
include_once("../Functions/accountFunctions.php");
include("../Functions/challange.php");
if(!checkIfLoggedIn()){
	header("Location: logIn.php");
}
challangePlayer();
?>
<!DOCTYPE html>
<html>
	<head>
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
		<?php
			listOfChallangedPlayers();
		?>
	</body>
</html>