<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
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
			<h1><?php dict("Replay")?></h1>
			<hr class="h1Line">
			<form action="../Pages/profile.php">
				<button type="submit"><?php dict("Profile")?></button>
			</form>
		</div>
	</body>
</html>