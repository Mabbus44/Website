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
		<label><b>Replay</b></label>
		<form action="../Pages/profile.php" method="post">
			<button type="submit">Profile</button>
		</form>
	</body>
</html>