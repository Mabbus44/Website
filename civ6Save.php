<?php
	//$encodedPost = array_keys($_POST)[0];
	//$encodedPost = array_keys($_POST)[0];
	$encodedPost = json_encode($_POST);

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

	//Clear database
	/*$stmt = $conn->prepare("DELETE FROM `Civ 6` WHERE 1");
	$stmt->execute();
	$stmt->close();*/

	//Insert into database
	$stmt = $conn->prepare("INSERT INTO `Civ 6` (val) VALUES (?)");
	$stmt->bind_param("s", $encodedPost);
	$stmt->execute();
	$stmt->close();

	$decodedPost = json_decode(array_keys($_POST)[0], true);
	$discordMessage["content"] = "Hey " . $decodedPost["value2"] . ", it´s your turn no. " . $decodedPost["value3"];

	//Insert into database
	$stmt = $conn->prepare("INSERT INTO `Civ 6` (val) VALUES (?)");
	$stmt->bind_param("s", $discordMessage["content"]);
	$stmt->execute();
	$stmt->close();

	$conn->close();

	$url = 'https://discordapp.com/api/webhooks/630437336576032785/zeap0vJNyYyC4Y5Ho6EmJAWcp5dGn3TsdHMPGkmnLY9_WYzOziP6G-f-G00H1stTWjfz';

	// use key 'http' even if you send the request to https://...
	$options = array(
			'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($discordMessage)
			)
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { /* Handle error */ }

	var_dump($result);
?>