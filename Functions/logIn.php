<?php
	function logIn(){
		if(DEBUG_INFO)
			er("logIn()");
		if(!isset($_POST) || !isset($_POST["password"]) || !isset($_POST["username"])){
			return;
		}
		include_once(__DIR__."/../Functions/commonFunctions.php");
		include_once(__DIR__."/../Functions/chessBoardFunctions.php");
		include_once(__DIR__."/../Functions/commonFunctions.php");
		session_start();

		//Conect to database
		$conn = dbCon();
		if(!$conn)
			return;

		//Read password hash from database
		$postPassword = $_POST["password"];
		$postUsername = $_POST["username"];
		$stmt = ps($conn, "SELECT password, id FROM tableName WHERE username=?", "credentials");
		$stmt->bind_param("s", $postUsername);
		if(!$stmt->execute()){
			er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT password, id FROM credentials WHERE username=?`");
			return;
		}
		$dbResult = $stmt->get_result();
		if($dbResult->num_rows == 0){
			echo "<script>alert(\"" . dictRet("Username does not exist") . "\");</script>";
			return;
		}
		if($dbResult->num_rows > 1){
			er("Multiple users with the same name found in logIn.php");
			return;
		}
		$row = $dbResult->fetch_assoc();
		$hashedPassword = $row["password"];
		//Decrypt and compare password hash
		list($hashedPassword, $enc_iv) = explode("::", $hashedPassword);
		$enc_iv = hex2bin($enc_iv);
		$hashedPassword = openssl_decrypt($hashedPassword, "aes-128-ctr", pepper(), 0, $enc_iv);
		if(!password_verify($postPassword, $hashedPassword)){
			unsetSession("username");
			echo "<script>alert(\"" . dictRet("Wrong password") . "\");</script>";
			return;
		}
		$stmt->close();
		$conn->close();
		setSession("username", $postUsername);
		setSession("id", $row["id"]);
		$nemesisInfo = getChessNemesisMatchIdAndStatus();
		if(is_null($nemesisInfo["nemesisId"])){
			//No chess nemesis
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
		}else{
			if($nemesisInfo["matchOver"]){
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/chessBoard.php");
			}else{
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/chessBoard.php?id=" . $nemesisInfo["matchId"]);
			}
		}
	}
?>