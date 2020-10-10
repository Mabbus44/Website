<?php
	session_start();
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/createAccount.php");
	createAccount();
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
			<h1><?php dict("Create account")?></h1>
			<hr class="h1Line">
			<form action="../Pages/createAccount.php" method="post">
				<input type="text" placeholder="<?php dict("Enter Username")?>" name="username" required>
				<input type="password" placeholder="<?php dict("Enter Password")?>" name="password" required>
				<input type="password" placeholder="<?php dict("Repeat Password")?>" name="password2" required>
				<button type="submit"><?php dict("Create account")?></button>
			</form>
		</div>
	</body>
</html>