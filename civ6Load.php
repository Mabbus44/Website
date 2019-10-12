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
	
	//Get
	$query = "SELECT `val` FROM `Civ 6` WHERE 1";
	$dbContent = $conn->query($query)->fetch_assoc()['val'];
	echo $dbContent;
?>