<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/choseColorFunctions.php");
	checkIfLoggedIn();
	handlePost();
?>
<!DOCTYPE html>
<html>
	<head>
		<!--<script type="text/javascript" src="../Js/main.js"></script>-->
		<title>Go</title>
	</head>
	<body>
		<label><b>Chose color</b></label>
		<form action="../Pages/choseColor.php" method="post">
			<button type="submit" name="black" value="1">Black</button>
			<button type="submit" name="white" value="1">White</button>
		</form>
	</body>
</html>