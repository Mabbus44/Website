<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	include_once(__DIR__."/../Functions/profileFunctions.php");
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
			<h1><?php dict("Profile")?></h1>
			<hr class="h1Line">
			<form action="../Pages/main.php">
				<button type="submit"><?php dict("Main")?></button>
			</form>
			<label id="matchHistory"><?php dict("Match history")?></label>
				<?php
					matchList();
				?>
		</div>
	</body>
</html>