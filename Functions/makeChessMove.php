<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");
checkIfLoggedIn();
include_once(__DIR__."/../Functions/chessBoardFunctions.php");
if(DEBUG_INFO)
	er("makeChessMove.php");


//convert strings to int and get player color
$move = array("fromX" => intval($_POST["fromX"]), "fromY" => intval($_POST["fromY"]), "toX" => intval($_POST["toX"]), "toY" => intval($_POST["toY"]));
$matchId = intval($_POST["matchId"]);
$colorAndId = getChessPlayerColorAndID($matchId);

//Check that parameters has correct values
if(!($move["fromX"] >= 0 && $move["fromY"] <= 7 && $move["toX"] >= 0 && $move["toY"] <= 7 && ($colorAndId["color"] == "white" || $colorAndId["color"] == "black"))){
	er("Invalid parameters in makeChessMove.php");
	echo json_encode($move["fromX"]);
	echo json_encode($move["fromY"]);
	echo json_encode($move["toX"]);
	echo json_encode($move["toY"]);
	echo json_encode($colorAndId["color"]);
	echo json_encode(array("error" => "invalid inputs"));
	exit();
}

//Conect to database
$conn = dbCon();
if(!$conn){
	echo json_encode(array("error" => "couldent connect to database"));
	exit();
}

//Read gamestate from database
$game = getChessBoard($matchId);

//Check that it is players turn
if(!((sizeof($game["moves"]) % 2 == 0 && $colorAndId["color"] == "white") || (sizeof($game["moves"]) % 2 == 1 && $colorAndId["color"] == "black"))){
	echo json_encode(array("error" => "not your turn"));
	exit();
}

//Check if move is valid
if(!isMoveValid($game, $move)){
	echo json_encode(array("error" => "invalid move"));
	exit();
}

//Perform move
performChessMove($move, $game["board"]);
$game["moves"][] = $move;
setAllValidMoves($game);
$game["checkStatus"] = getCheckState($game);
$game["color"] = "black";
$moveCount = sizeof($game["moves"]);
if($moveCount % 2 == 0)
	$game["color"] = "white";

//Insert new move into database
$moveCount--;
$stmt = ps($conn, "INSERT INTO `tableName` (`fromX`, `fromY`, `toX`, `toY`, `moveIndex`, `matchIndex`) VALUES (?,?,?,?,?,?)", "currentChessGames");
$stmt->bind_param("iiiiii", $move["fromX"], $move["fromY"], $move["toX"], $move["toY"], $moveCount, $matchId);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `currentChessGames` (`fromX`, `fromY`, `toX`, `toY`, `moveIndex`, `matchId`) VALUES (?,?,?,?,?,?)`");
	echo json_encode(array("error" => "prepared statement failed"));
	exit();
}
$stmt->close();

//If checkmate or draw, end the game
if($game["checkStatus"] == "checkmate" || $game["checkStatus"] == "draw"){
	$stmt = ps($conn, "UPDATE `tableName` SET `winner`=?, `endCause`=? WHERE `matchIndex` = ?", "chessMatchList");
	$winnerId = null;
	$endCauseVal = 1;	//1 = draw, 2 = surrender (not implemented), 3 = check mate
	if($game["checkStatus"] == "checkmate"){
		if(sizeof($game["moves"]) % 2 == 0){
			$winnderId = $game["blackId"];
		}else{
			$winnderId = $game["whiteId"];
		}
		$endCauseVal = 3;
	}
	$stmt->bind_param("iii", $winnderId, $endCauseVal, $matchId);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `UPDATE `chessMatchList` SET `winner`=?, `endCause`=? WHERE `matchIndex` = ?`");
		exit();
	}
	$stmt->close();
}

//Return new board
echo json_encode($game);
$conn->close();
?>