<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");
checkIfLoggedIn();
include_once(__DIR__."/../Functions/boardFunctions.php");
if(DEBUG_INFO)
	er("giveUp.php");

//convert strings to int and get player color
$matchIndex = intval($_POST["matchID"]);
$color = getPlayerColor($matchIndex);

//Check that parameters is correct
if(!isset($color)){
	er("Missing color in giveUp.php");
	exit();
}

//Check that parameters has correct values
if(!($color>=0 && $color<=1)){
	er("Invalid value of $color (" . $color . ") in giveUp.php");
	exit();
}

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Read gamestate from database
$board = getBoardExistingCon($conn, $matchIndex);
if(array_key_exists("error", $board)){
	er("Error when getting board in giveUp.php");
	exit();
}

//Check if it is the colors turn
if($board["lastColor"] == $color){
	$result["info"] = dictRet("It´s not your turn");
	echo json_encode($result);
	exit();
}

//Insert new move into database
$action = 3;
$stmt = ps($conn, "INSERT INTO `tableName` (`action`, `moveIndex`, `matchIndex`) VALUES (?,?,?)", "currentGames");
$stmt->bind_param("iii", $action, $board["currMove"], $matchIndex);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `currentGames` (`action`, `moveIndex`, `matchIndex`) VALUES (?,?,?)`");
	exit();
}
$stmt->close();

//End game
$result = endGame($matchIndex, 2);
$conn->close();
$result["info"] = dictRet("Surrender winner", [$result["loserName"], $result["winnerName"]]);
$result["action"] = "game ended";
echo json_encode($result);
?>