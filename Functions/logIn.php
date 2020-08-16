<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");
session_start();
if(DEBUG_INFO)
	er("logIn.php");

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Read password hash from database
$postPassword = $_POST["password"];
$postUsername = $_POST["username"];
$stmt = ps($conn, "SELECT password, id FROM tableName WHERE username=?", "credentials");
$stmt->bind_param("s", $postUsername);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT password, id FROM credentials WHERE username=?`");
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows == 0){
	exit(dictRet("Username does not exist"));
}
if($dbResult->num_rows > 1){
	er("Multiple users with the same name found in logIn.php");
	exit();
}
$row = $dbResult->fetch_assoc();
$hashedPassword = $row["password"];
//Decrypt and compare password hash
list($hashedPassword, $enc_iv) = explode("::", $hashedPassword);
$enc_iv = hex2bin($enc_iv);
$hashedPassword = openssl_decrypt($hashedPassword, "aes-128-ctr", pepper(), 0, $enc_iv);
if(!password_verify($postPassword, $hashedPassword)){
	unsetSession("username");
	exit(dictRet("Wrong password"));
}
$stmt->close();
$conn->close();
setSession("username", $postUsername);
setSession("id", $row["id"]);
header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
?>