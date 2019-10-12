<?php
include "boardFunctions.php";
$servername = "rasmus.today.mysql";
$username = "rasmus_today";
$password = "9Nah5fEsDTayJ5doJVaXuAb6";
$dbname = "rasmus_today";

//convert strings to int
$x = intval($_POST["x"]);
$y = intval($_POST["y"]);
$color = intval($_POST["color"]);

//Check that parameters is correct
if(!isset($x) or !isset($y) or !isset($color)){
	$result["error"] = "error: Missing parameters";
	echo json_encode($result);
	exit();
}
//Check that parameters has correct values
if(!($x>=0 && $x<=18 && $y>=0 && $y<=18 && $color>=0 && $color<=1)){
	$result["error"] = "error: Invalid parameters";
	echo json_encode($result);
	exit();
}
//Conect to database
$conn = mysqli_connect($servername, $username, $password, $dbname);
if(!$conn){
	$result["error"] = "error: Database connection error --- " . $conn.error;
	echo json_encode($result);
	exit();
}
//Read gamestate from database
$query = "SELECT * FROM currentGame";
$gameState = $conn->query($query);
if($conn->errno){
	$result["error"] = "error: Could not select from database --- " . $query . " --- " . $conn->error;
	echo json_encode($result);
	exit();
}
$board = getBoardExistingCon($conn);
if(array_key_exists("error", $board)){
	$result["error"] = board["error"];
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
//Check is spot is taken
if($board["board"][$x][$y] != 2){
	$result["info"] = "info: spot taken";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
//Check if it is the colors turn
if($board["lastColor"] != $color && validColor($board["board"], $color)){
}
elseif($board["lastColor"] == $color && !validColor($board["board"], 1-$color) && validColor($board["board"], $color)){
}
if($board["lastColor"] == $color){
	$result["info"] = "info: not your turn";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
//Check if the placed stone is surrounded
if(!validSquare($board["board"], $x, $y, $color)){
	$result["info"] = "info: you may not surround yourself";
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
//Insert new move into database
$query = "INSERT INTO currentGame (x, y, color, moveIndex) VALUES (" . $x . ", " . $y . ", " . $color . ", " . $board["currMove"] . ")";
if(!$conn->query($query)){
	$result["error"] = "error: Could not insert query --- " . $query . " --- " . $conn->error;
	mysqli_close($conn);
	echo json_encode($result);
	exit();
}
$result["info"] = "info: move added";
echo json_encode($result);
mysqli_close($conn);
?>