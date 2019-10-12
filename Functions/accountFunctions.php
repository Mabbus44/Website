<?php
function checkIfLoggedIn(){
	session_start();
	if(isset($_SESSION["username"])){
		return true;
	}
	return false;
}

function getNameFromID($id){
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

	//Find username
	$stmt = $conn->prepare("SELECT `username`, `id` FROM `credentials` WHERE `id` = ?");
	$stmt->bind_param("i", $id);
	if(!$stmt->execute()){
		$result["error"] = "error: Could note execute prepared statement";
		echo json_encode($result);
		exit();
	}
	$result = $stmt->get_result();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		return $row["username"];
	}
	return "";
}
?>