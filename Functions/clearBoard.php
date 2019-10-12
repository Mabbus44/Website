<?php
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
//clear table
$query = "TRUNCATE TABLE currentGame";
$gameState = $conn->query($query);
if($conn->errno){
	$result["error"] = "error: Could not clear table --- " . $query . " --- " . $conn->error;
	echo json_encode($result);
	exit();
}

$result["info"] = "info: table cleared";
echo json_encode($result);
mysqli_close($conn);
?>