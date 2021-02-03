<?php
	session_start();
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/logIn.php");
	logIn();
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../Css/main.css">
		<script type="text/javascript" src="../Js/jQuery.js"></script>
		<script type="text/javascript" src="../Js/changeLanguage.js"></script>
		<title><?php dict("Go")?></title>
	</head>
	<body>
		<?php topPanel()?>
		<div class="contentDiv">
			<h1><?php dict("Log in")?></h1>
			<hr class="h1Line">
			<form action="../Pages/logIn.php" method="post">
				<input type="text" placeholder="<?php dict("Enter Username")?>" name="username" required>
				<input type="password" placeholder="<?php dict("Enter Password")?>" name="password" required>
				<button type="submit"><?php dict("Log in")?></button>
			</form>
			<hr>
			<form action="../Pages/createAccount.php">
				<button type="submit"><?php dict("Create account")?></button>
			</form>
		</div>
	</body>
</html>