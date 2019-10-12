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
//Check if usename is valid
if(false){
	$result["error"] = "error: Invalid username";
	echo json_encode($result);
	exit();
}
//Check if password is valid
if(false){
	$result["error"] = "error: Invalid password";
	echo json_encode($result);
	exit();
}
//Add salt, encrypt, add pepper
$hashedPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);
$enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-128-ctr"));
$pepperedPassword = openssl_encrypt($hashedPassword, "aes-128-ctr", "Zu82HUsrKc7xuxBCqvUW", 0, $enc_iv) . "::" . bin2hex($enc_iv);
$postUsername = $_POST["username"];

//Get next free user ID
$query = "SELECT `value` FROM `settings` WHERE `setting` = 'freeUserID'";
$id = $conn->query($query)->fetch_assoc()['value'];
if(!isset($id) or $id<1){
	$result["error"] = "error: Could not get free id " . $id;
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}

//Insert username and password into database
$stmt = $conn->prepare("INSERT INTO credentials (username, password, id) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $postUsername, $pepperedPassword, $id);
if(!$stmt->execute()){
	$result["error"] = "error: Could not insert credentials into database ";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
$stmt->close();

//Increase freeID by one
$id = $id+1;
$stmt = $conn->prepare("UPDATE `settings` SET `value`=(?) where `setting` = 'freeUserID'");
$stmt->bind_param("i", $id);
if(!$stmt->execute()){
	$result["error"] = "error: Could not infcrease free id ";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}

$stmt->close();
$conn->close();
$_SESSION["username"] = $postUsername;
$_SESSION["id"] = $id;
header("Location: ../Pages/main.php");
?>