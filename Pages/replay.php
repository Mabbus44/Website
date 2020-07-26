<?php
	include_once(__DIR__."/../Functions/accountFunctions.php");
	checkIfLoggedIn();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Go</title>
	</head>
	<body>
		<label><b>Replay</b></label>
		<form action="../Pages/profile.php" method="post">
			<button type="submit">Profile</button>
		</form>
	</body>
</html>