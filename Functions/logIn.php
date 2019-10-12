<?php
session_start();
$servername = "rasmus.today.mysql";
$username = "rasmus_today";
$password = "9Nah5fEsDTayJ5doJVaXuAb6";
$dbname = "rasmus_today";

//Conect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if(!$conn){
	$result["error"] = "error: Database connection error --- " . $conn.error;
	echo json_encode($result);
	exit();
}
//Read password hash from database
$postPassword = $_POST["password"];
$postUsername = $_POST["username"];
$stmt = $conn->prepare("SELECT password, id FROM credentials WHERE username=?");
$stmt->bind_param("s", $postUsername);
if(!$stmt->execute()){
	$result["error"] = "error: Could not execute statement";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows != 1){
	$result["error"] = "error: Db returned " . $dbResult->num_rows . " rows, should be 1";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
$row = $dbResult->fetch_assoc();
$hashedPassword = $row["password"];
//Decrypt and compare password hash
list($hashedPassword, $enc_iv) = explode("::", $hashedPassword);
$enc_iv = hex2bin($enc_iv);
$hashedPassword = openssl_decrypt($hashedPassword, "aes-128-ctr", "Zu82HUsrKc7xuxBCqvUW", 0, $enc_iv);
if(!password_verify($postPassword, $hashedPassword)){
	unset($_SESSION["username"]);
	$result["info"] = "info: Wrong password";
	echo json_encode($result);
	exit();
}
$stmt->close();
$conn->close();
$_SESSION["username"] = $postUsername;
$_SESSION["id"] = $row["id"];
header("Location: ../Pages/main.php");
?>