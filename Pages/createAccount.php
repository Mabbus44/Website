<!DOCTYPE html>
<html>
	<head>
		<title>Go</title>
	</head>
	<body>
		<form action="../Functions/createAccount.php" method="post">
			<label for="username"><b>Username</b></label>
			<input type="text" placeholder="Enter Username" name="username" required>
			<label for="password"><b>Password</b></label>
			<input type="password" placeholder="Enter Password" name="password" required>
			<button type="submit">Create account</button>
		</form>
		<form action="../Pages/logIn.php" method="post">
			<button type="submit">Login</button>
		</form>
	</body>
</html>