<?php
include_once(__DIR__."/../Functions/commonFunctions.php");

session_start();

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Check if usename is valid
if(!preg_match("/^[\\p{L}0-9_-]{2,255}$/u", $_POST["username"])) {
	exit("Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)");
}

//Check if password is valid
if(!preg_match("/^[\\p{L}0-9_-]{3,255}$/u", $_POST["password"])){
	exit("Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)");
}
//Add salt, encrypt, add pepper
$hashedPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);
$enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-128-ctr"));
$pepperedPassword = openssl_encrypt($hashedPassword, "aes-128-ctr", pepper(), 0, $enc_iv) . "::" . bin2hex($enc_iv);
$postUsername = $_POST["username"];

//Insert username and password into database
$stmt = ps($conn, "INSERT INTO `tableName` (username, password) VALUES (?, ?)", "credentials");
$stmt->bind_param("ss", $postUsername, $pepperedPassword);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `credentials` (username, password) VALUES (?, ?)`");
	exit();
}
$stmt->close();


//Get user ID
$stmt = ps($conn, "SELECT id FROM tableName WHERE username=?", "credentials");
$stmt->bind_param("s", $postUsername);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . "SELECT id FROM credentials WHERE username=?");
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows == 0){
	er("Newly created user did not exist in crateAccount.php");
	exit();
}
if($dbResult->num_rows > 1){
	er("Multiple users with the same name found in crateAccount.php");
	exit();
}
$row = $dbResult->fetch_assoc();
$stmt->close();
$conn->close();

setSession("username", $postUsername);
setSession("id", $row["id"]);
header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
?>