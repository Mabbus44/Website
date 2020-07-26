<?php
include_once(__DIR__."/../Functions/commonFunctions.php");

function checkIfLoggedIn(){
	session_start();
	if(issetSession("username")){
		return true;
	}
	echo header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/logIn.php");
	return false;
}

function getNameFromID($id){
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Find username
	$stmt = ps($conn, "SELECT `username`, `id` FROM `tableName` WHERE `id` = ?", "credentials");
	$stmt->bind_param("i", $id);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username`, `id` FROM `credentials` WHERE `id` = ?`");
		exit();
	}
	$result = $stmt->get_result();
	$stmt->close();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		return $row["username"];
	}
	$conn->close();
	return "";
}
?>