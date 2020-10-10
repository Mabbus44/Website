<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");
checkIfLoggedIn();
include_once(__DIR__."/../Functions/boardFunctions.php");
if(DEBUG_INFO)
	er("placeStone.php");


//convert strings to int and get player color
$x = intval($_POST["x"]);
$y = intval($_POST["y"]);
$matchIndex = intval($_POST["matchID"]);
$color = getPlayerColor($matchIndex);

//Check that parameters is correct
if(!isset($x) or !isset($y) or !isset($color)){
	er("Missing parameters in placeStone.php");
	exit();
}
//Check that parameters has correct values
if(!($x>=0 && $x<=18 && $y>=0 && $y<=18 && $color>=0 && $color<=1)){
	er("Invalid parameters in placeStone.php $x(" . $x . ") $y(" . $y . ") $color(" . $color . ")");
	exit();
}

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Read gamestate from database
$board = getBoardExistingCon($conn, $matchIndex);
if(array_key_exists("error", $board)){
	er("Error when getting board in placeStone.php");
	exit();
}
//Check if it is the colors turn
if($board["lastColor"] == $color){
	$result["info"] = dictRet("It´s not your turn");
	echo json_encode($result);
	exit();
}
//Check if the placed stone is on a valid square
if(!validSquare($board["board"], $board["oldBoard"], $x, $y, $color)){
	er("ValidSquare = False");
	$result["info"] = dictRet("Invalid placement");
	$result["boards"] = $board;
	echo json_encode($result);
	exit();
}
er("ValidSquare = True");
//Insert new move into database
$action = 1;
$stmt = ps($conn, "INSERT INTO `tableName` (`x`, `y`, `action`, `moveIndex`, `matchIndex`) VALUES (?,?,?,?,?)", "currentGames");
$stmt->bind_param("iiiii", $x, $y, $action, $board["currMove"], $matchIndex);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `currentGames` (`x`, `y`, `action`, `moveIndex`, `matchIndex`) VALUES (?,?,?,?)`");
	exit();
}
$stmt->close();
$result["info"] = dictRet("Stone added");
$result["x"] = $x;
$result["y"] = $y;
$result["action"] = "Stone added";
echo json_encode($result);
$conn->close();
?>