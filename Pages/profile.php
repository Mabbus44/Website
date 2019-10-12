<?php
include("../Functions/accountFunctions.php");
if(!checkIfLoggedIn()){
	header("Location: logIn.php");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Go</title>
	</head>
	<body>
		<label><b>Profile</b></label>
		<form action="../Pages/main.php" method="post">
			<button type="submit">Main</button>
		</form>
		<form action="../Pages/replay.php" method="post">
			<button type="submit">replay</button>
		</form>
	</body>
</html>