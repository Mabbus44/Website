<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");
checkIfLoggedIn();
include_once(__DIR__."/../Functions/boardFunctions.php");

//convert strings to int and get player color
$matchIndex = intval($_POST["matchID"]);
$color = getPlayerColor($matchIndex);

//Check that parameters is correct
if(!isset($color)){
	er("Missing color in passTurn.php");
	exit();
}

//Check that parameters has correct values
if(!($color>=0 && $color<=1)){
	er("Invalid value of $color (" . $color . " in passTurn.php");
	exit();
}

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Read gamestate from database
$board = getBoardExistingCon($conn, $matchIndex);
if(array_key_exists("error", $board)){
	er("Error when getting board in passTurn.php");
	exit();
}

//Check if it is the colors turn
if($board["lastColor"] == $color){
	$result["info"] = "It´s not your turn";
	echo json_encode($result);
	exit();
}

//Insert new move into database
$action = 2;
$stmt = ps($conn, "INSERT INTO `tableName` (`action`, `moveIndex`, `matchIndex`) VALUES (?,?,?)", "currentGames");
$stmt->bind_param("iii", $action, $board["currMove"], $matchIndex);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `currentGames` (`action`, `moveIndex`, `matchIndex`) VALUES (?,?,?)`");
	exit();
}

//End the game if two passes in a row
$stmt->close();
$lastMove = $board["currMove"] - 1;
$stmt = ps($conn, "SELECT `action` FROM `tableName` WHERE `matchIndex` = ? AND `moveIndex` = ?", "currentGames");
$stmt->bind_param("ii", $matchIndex, $lastMove);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `action` FROM `currentGames` WHERE `matchIndex` = ? AND `moveIndex` = ?`");
	exit();
}
$action = $stmt->get_result();
$stmt->close();
if($action->num_rows == 1) {
	$row = $action->fetch_assoc();
	if($row["action"] == "pass") {
		$result = endGame($matchIndex, 1);
		if(array_key_exists("error", $result)){
			er("Function endGame returned error in passTurn.php");
			exit();
		}
		$result["info"] = "Game ended. " . $result["winner"] . " won. Score: " . $result["points1"] . "-" . $result["points2"];
		echo json_encode($result);
		exit();
	}
}

$result["info"] = "Turn passed";
echo json_encode($result);
$conn->close();
?>