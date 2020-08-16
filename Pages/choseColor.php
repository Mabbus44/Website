<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/choseColorFunctions.php");
	checkIfLoggedIn();
	handlePost();
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<title><?php dict("Go")?></title>
	</head>
	<body>
		<?php topPanel()?>
		<div class="contentDiv">
			<h1><?php dict("Chose color")?></h1>
			<hr class="h1Line">
			<form action="../Pages/choseColor.php" method="post">
				<button type="submit" name="black" value="1"><?php dict("Black")?></button>
				<button type="submit" name="white" value="1"><?php dict("White")?></button>
			</form>
		</div>
	</body>
</html>