<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");

//Get your chess nemesis
function getChessNemesis(){
	$id = getSession("id");
	$chessNemeses = array();
	$chessNemeses[] = array(13, 14);
	$chessNemeses[] = array(15, 16);
	foreach($chessNemeses as $chessNemesis){
		if($id == $chessNemesis[0])
			return $chessNemesis[1];
		if($id == $chessNemesis[1])
			return $chessNemesis[0];
	}
	return null;
}

//Get next matchId with your nemesis
function getChessNemesisMatchIdAndStatus(){
	$nemesisId = getChessNemesis();
	if($nemesisId == null){
		return array("matchId" => null, "matchOver" => true, "nemesisId" => null);
	}

	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Check if users is already in a game
	$stmt = ps($conn, "SELECT `matchIndex`, `endCause` FROM `tableName` WHERE ((`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)) ORDER BY `matchIndex` DESC LIMIT 1", "chessMatchList");
	$stmt->bind_param("iiii", getSession("id"), $nemesisId, $nemesisId, getSession("id"));
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `matchIndex`, `endCause` FROM `chessMatchList` WHERE ((`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)) ORDER BY `matchIndex` DESC LIMIT 1`");
		exit();
	}
	$result = $stmt->get_result();
	if($result->num_rows == 0){
		return array("matchId" => null, "matchOver" => true, "nemesisId" => $nemesisId);
	}
	$row = $result->fetch_assoc();
	$stmt->close();
	$conn->close();
	if($row["endCause"] == null)
		$matchOver = false;
	else
		$matchOver = true;
	return array("matchId" => $row["matchIndex"], "matchOver" => $matchOver, "nemesisId" => $nemesisId);
}

//Check if move exist in database
function chessMoveExist($matchIndex, $moveIndex){
	if(DEBUG_INFO)
		er("chessMoveExist()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Check for move in database
	$stmt = ps($conn, "SELECT EXISTS(SELECT * FROM `tableName` WHERE `matchIndex` = ? AND `moveIndex` >= ?)", "currentChessGames");
	$stmt->bind_param("ii", $matchIndex, $moveIndex);

	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT TOP 1 FROM `tableName` WHERE `matchIndex` == ? AND `moveIndex` >= ?`");
		exit();
	}
	$move = $stmt->get_result();
	$stmt->close();
	$conn->close();
	
	//Return result
	$result = false;
	if($move->num_rows > 0) {
		$row = $move->fetch_assoc();
		$objIterator = new ArrayIterator($row);
		$id = $objIterator->current();
		if($id){
			$result = true;
		}
	}
	return $result;
}

//Gets all moves of a match
function getAllChessMoves($matchIndex){
	if(DEBUG_INFO)
		er("getAllChessMoves()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Read moves from database
	$stmt = ps($conn, "SELECT `fromX`, `fromY`, `toX`, `toY` FROM `tableName` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC", "currentChessGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `fromX`, `fromY`, `toX`, `toY` FROM `tableName` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC", "currentChessGames`");
		exit();
	}
	
	//Extract and return moves
	$moves = $stmt->get_result();
	$stmt->close();
	$rowNum = 0;
	$result = array();
	if($moves->num_rows > 0) {
		while($row = $moves->fetch_assoc()) {
			$result[$rowNum] = $row;
			$rowNum = $rowNum + 1;
		}
	}
	$conn->close();
	return $result;
}

//Gets x moves of a match
function getSomeChessMoves($matchIndex, $moveIndex){
	if(DEBUG_INFO)
		er("getSomeChessMoves()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Read moves from database
	$stmt = ps($conn, "SELECT `fromX`, `fromY`, `toX`, `toY` FROM `tableName` WHERE `matchIndex` = ? AND `moveIndex` <= ? ORDER BY `moveIndex` ASC", "currentChessGames");
	$stmt->bind_param("ii", $matchIndex, $moveIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `fromX`, `fromY`, `toX`, `toY` FROM `tableName` WHERE `matchIndex` = ? AND `moveIndex` <= ? ORDER BY `moveIndex` ASC", "currentChessGames`");
		exit();
	}
	
	//Extract and return moves
	$moves = $stmt->get_result();
	$stmt->close();
	$rowNum = 0;
	$result = array();
	if($moves->num_rows > 0) {
		while($row = $moves->fetch_assoc()) {
			$result[$rowNum] = $row;
			$rowNum = $rowNum + 1;
		}
	}
	$conn->close();
	return $result;
}

//Gets all moves from db and transform them into a board
function getChessBoard($matchIndex){
	if(DEBUG_INFO)
		er("getChessBoard()");
	
	//Get all moves
	$game = array();
	$game["moves"] = getAllChessMoves($matchIndex);
	
	//Setup starting board
	$game["board"] = getStartingChessBoard();

	//Make the moves
	for($i=0; $i<sizeof($game["moves"]); $i++){
		performChessMove($game["moves"][$i], $game["board"]);
	}
	
	setAllValidMoves($game);
	$game["checkStatus"] = getCheckState($game);
	$game["color"] = "black";
	if(sizeof($game["moves"]) % 2 == 0)
		$game["color"] = "white";
	$playerIds = getPlayerIds($matchIndex);
	$game["whiteId"] = $playerIds[0];
	$game["blackId"] = $playerIds[1];
	$game["whiteName"] = getNameFromID($game["whiteId"]);
	$game["blackName"] = getNameFromID($game["blackId"]);
	return $game;
}

//Gets x moves from db and transform them into a board
function getChessBoardAtMove($matchIndex, $moveIndex){
	if(DEBUG_INFO)
		er("getChessBoardAtMove()");
	
	//Get x moves
	$game = array();
	$game["moves"] = getSomeChessMoves($matchIndex, $moveIndex);
	
	//Setup starting board
	$game["board"] = getStartingChessBoard();

	//Make the moves
	for($i=0; $i<sizeof($game["moves"]); $i++){
		performChessMove($game["moves"][$i], $game["board"]);
	}
	
	setAllValidMoves($game);
	$game["checkStatus"] = getCheckState($game);
	$game["color"] = "black";
	if(sizeof($game["moves"]) % 2 == 0)
		$game["color"] = "white";
	$playerIds = getPlayerIds($matchIndex);
	$game["whiteId"] = $playerIds[0];
	$game["blackId"] = $playerIds[1];
	$game["whiteName"] = getNameFromID($game["whiteId"]);
	$game["blackName"] = getNameFromID($game["blackId"]);
	return $game;
}

//Get id´s of players in match
function getPlayerIds($matchIndex){
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get player IDs
	$stmt = ps($conn, "SELECT `player1ID`, `player2ID` FROM `tableName` WHERE `matchIndex` = ?", "chessMatchList");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " SELECT `player1ID`, `player2ID` FROM `chessMatchList` WHERE `matchIndex` = ?");
		exit();
	}
	$dbResult = $stmt->get_result();
	if($dbResult->num_rows == 0){
		er("Didnt find any match with id " . $matchIndex . " in chessBoardFunctions.php");
		exit();
	}
	$row = $dbResult->fetch_assoc();
	$stmt->close();
	$conn->close();
	$result = array($row["player1ID"], $row["player2ID"]);
	return $result;
}

//Gets chess board in starting position
function getStartingChessBoard(){
	if(DEBUG_INFO)
		er("getStartingChessBoard()");
	$board = array();
	$board[0] = explode(" ", "VT VH VL VD VK VL VH VT");
	$board[1] = explode(" ", "VB VB VB VB VB VB VB VB");
	for($i=2; $i<6; $i++){
		$board[$i] = array_fill(0, 8, "");
	}
	$board[6] = explode(" ", "SB SB SB SB SB SB SB SB");
	$board[7] = explode(" ", "ST SH SL SD SK SL SH ST");
	return $board;
}

//Performs supplied move on supplied board (without checking if it is a valid chess move)
function performChessMove($move, &$board){
	if(DEBUG_INFO)
		er("performChessMove()");
	$board[$move["toY"]][$move["toX"]] = $board[$move["fromY"]][$move["fromX"]];
	$board[$move["fromY"]][$move["fromX"]] = "";
	
	//Get color of mover
	$color = "V";
	if(str_contains($board[$move["toY"]][$move["toX"]], "S")){
		$color = "S";
	}

	//Pawn went to oposite side and became queen
	if(str_contains($board[$move["toY"]][$move["toX"]], "B") && ($move["toY"] == 0 || $move["toY"] == 7)){
		$board[$move["toY"]][$move["toX"]] = $color . "D";
	}

	//King jumped to rook
	if(str_contains($board[$move["toY"]][$move["toX"]], "K") && abs($move["toX"] - $move["fromX"]) == 2){
		//Right rook
		if($move["toX"] > $move["fromX"]){
			$board[$move["toY"]][5] = $board[$move["toY"]][7];
			$board[$move["toY"]][7] = "";
		}else{
			//Left rook
			$board[$move["toY"]][3] = $board[$move["toY"]][0];
			$board[$move["toY"]][0] = "";
		}
	}
}

//Sets all valid next moves of game
function setAllValidMoves(&$game){
	if(DEBUG_INFO)
		er("setAllValidMoves()");
	$game["possibleMoves"] = array();
	for($y = 0; $y < 8; $y++){
		for($x = 0; $x < 8; $x++){
			setAllValidMovesForPiece($game, $x, $y);
		}	
	}
}

//Sets all valid next moves for position
function setAllValidMovesForPiece(&$game, $x, $y){
	if(DEBUG_INFO)
		er("setAllValidMovesForPiece()");
	$showDebug = true;
	$board = &$game["board"];
	
	//Get color of mover
	$color = "V";
	$dir = 1;
	if(sizeof($game["moves"]) % 2 == 1){
		$color = "S";
		$dir = -1;
	}

	//Check if position contains piece of right player
	if(!str_contains($board[$y][$x], $color)){
		return;
	}

	//Only move one step for king
	$oneStep = false;
	if(str_contains($board[$y][$x], "K")){
		$oneStep = true;
	}

	//Get all possible destinations
	$destinations = array();
	if(	str_contains($board[$y][$x], "T") ||
			str_contains($board[$y][$x], "D") ||
			str_contains($board[$y][$x], "K")){
		array_push($destinations, ...getSquares($x, $y, 1, 0, $oneStep));
		array_push($destinations, ...getSquares($x, $y, -1, 0, $oneStep));
		array_push($destinations, ...getSquares($x, $y, 0, 1, $oneStep));
		array_push($destinations, ...getSquares($x, $y, 0, -1, $oneStep));
	}
	if(	str_contains($board[$y][$x], "L") ||
						str_contains($board[$y][$x], "D") ||
						str_contains($board[$y][$x], "K")){
		array_push($destinations, ...getSquares($x, $y, 1, 1, $oneStep));
		array_push($destinations, ...getSquares($x, $y, -1, 1, $oneStep));
		array_push($destinations, ...getSquares($x, $y, 1, -1, $oneStep));
		array_push($destinations, ...getSquares($x, $y, -1, -1, $oneStep));
	}
	if(	str_contains($board[$y][$x], "H")){
		array_push($destinations, ...getSquare($x-2, $y-1));
		array_push($destinations, ...getSquare($x-2, $y+1));
		array_push($destinations, ...getSquare($x+2, $y-1));
		array_push($destinations, ...getSquare($x+2, $y+1));
		array_push($destinations, ...getSquare($x-1, $y-2));
		array_push($destinations, ...getSquare($x+1, $y-2));
		array_push($destinations, ...getSquare($x-1, $y+2));
		array_push($destinations, ...getSquare($x+1, $y+2));
	}
	if(str_contains($board[$y][$x], "B")){
		array_push($destinations, ...getSquare($x, $y + $dir));
		array_push($destinations, ...getSquare($x, $y + $dir * 2));
		array_push($destinations, ...getSquare($x + 1, $y + $dir));
		array_push($destinations, ...getSquare($x - 1, $y + $dir));
	}
	
	//Only return valid destinations
	for($i = 0; $i < sizeof($destinations); $i++){
		$move = array("fromX" => $x, "fromY" => $y, "toX" => $destinations[$i]["x"], "toY" => $destinations[$i]["y"]);
		if(isMoveValid($game, $move)){
			$game["possibleMoves"][] = $move;
		}
	}
}

//Get squares in one direction
function getSquares($x, $y, $dX, $dY, $oneStep = false){
	if(DEBUG_INFO)
		er("getSquares()");
	$squares = array();
	$squareId = 0;
	$x = $x + $dX;
	$y = $y + $dY;	
	while(inBounds($x, $y)){
		$squares[$squareId] = array("x"=>$x, "y"=>$y);
		$squareId++;
		$x = $x + $dX;
		$y = $y + $dY;
		if($oneStep)
			$x = $x + 100;
	}
	return $squares;
}

//Gets a single square if inbound. The square is put inside an array to match format of getSquares
function getSquare($x, $y){
	if(DEBUG_INFO)
		er("getSquare()");
	if(inBounds($x, $y)){
		return array(array("x"=>$x, "y"=>$y));
	}
	return array();
}

//Checks that coordinates is inside board
function inBounds($x, $y){
	if(DEBUG_INFO)
		er("inBounds()");
	if($x < 0 || $y < 0 || $x > 7 || $y >7)
		return false;
	return true;	
}

//Checks if move is valid for board
function isMoveValid(&$game, $move){
	if(DEBUG_INFO)
		er("isMoveValid()");
	$board = &$game["board"];
	$x = $move["fromX"];
	$y = $move["fromY"];
	$toX = $move["toX"];
	$toY = $move["toY"];
	$dx = sign($toX - $x);
	$dy = sign($toY - $y);
	$diffX = abs($toX - $x);
	$diffY = abs($toY - $y);
	$distX = $toX - $x;
	$distY = $toY - $y;

	//Get color of mover
	$color = "V";
	$oponentColor = "S";
	$pawnDir = 1;
	if(sizeof($game["moves"]) % 2 == 1){
		$color = "S";
		$oponentColor = "V";
		$pawnDir = -1;
	}

	//Check if move is out of bounds
	if(!inBounds($x, $y) || !inBounds($toX, $toY))
		return false;
	
	//Check if position contains piece of right player
	if(!str_contains($board[$y][$x], $color))
		return false;

	//Check if destination dont contain piece of moving player
	if(str_contains($board[$toY][$toX], $color))
		return false;

	//Check piece unique rules
	if(!str_contains($board[$y][$x], "H")){
		if(!isPathClear($board, $x, $y, $toX, $toY)){
			return false;
		}
	}
	if(str_contains($board[$y][$x], "T")){
		if($x != $toX && $y != $toY)
			return false;
	}elseif(str_contains($board[$y][$x], "H")){
		if(!(($diffX == 1 && $diffY == 2) || ($diffX == 2 && $diffY == 1)))
			return false;
	}elseif(str_contains($board[$y][$x], "L")){
		if($diffX != $diffY)
			return false;
	}elseif(str_contains($board[$y][$x], "D")){
		if($diffX != 0 && $diffY != 0 && $diffX != $diffY)
			return false;
	}elseif(str_contains($board[$y][$x], "K")){
		if($diffX > 1 || $diffY > 1)
			return false;
	}elseif(str_contains($board[$y][$x], "B")){
		if(!(
				($distY == $pawnDir && $distX == 0 && $board[$toY][$toX] == "") ||
				($distY == $pawnDir && $diffX == 1 && str_contains($board[$toY][$toX], $oponentColor)) ||
				($distY == $pawnDir * 2 && $diffX == 0 && $board[$toY][$toX] == "" && ($y == 1 || $y == 6))
				)){
			//er("Pawn not move (" . $x . "," . $y . ")-(" . $toX . "," . $toY . ") " . $distY . ", " . $pawnDir . ", " . );
			return false;
		}
	}

	//Check if player checked themself
	$boardCopy = $board;
	performChessMove($move, $boardCopy);
	if(isChecked($boardCopy, $color)){
		return false;
	}
	return true;
}

//Checks if path is clear
function isPathClear(&$board, $x, $y, $toX, $toY){
	if(DEBUG_INFO)
		er("isPathClear()");
	$dx = sign($toX - $x);
	$dy = sign($toY - $y);
	
	//No move
	if($dx == 0 && $dy == 0)
		return true;

	//No straight path
	if($dx != 0 && $dy != 0 && abs($toX - $x) != abs($toY - $y))
		return false;

	//Check squares between start and finnish
	$x = $x + $dx;
	$y = $y + $dy;
	while($x != $toX || $y != $toY){
		if($board[$y][$x] != "")
			return false;
		$x = $x + $dx;
		$y = $y + $dy;
	}

	return true;
}

//Checks if check, checkmate or draw
function getCheckState(&$game){
	if(DEBUG_INFO)
		er("getCheckState()");
	//Check whos turn it is
	$color = "S";
	if(sizeof($game["moves"]) % 2 == 0)
		$color = "V";
	$board = &$game["board"];

	//Find that players king
	$kingName = $color . "K";
	$kingX = -1;
	$kingY = -1;
	for($y = 0; $y < 8 && $kingX == -1; $y++){
		for($x = 0; $x < 8 && $kingX == -1; $x++){
			if($board[$y][$x] == $kingName){
				$kingX = $x;
				$kingY = $y;
			}
		}
	}
	if($kingX == -1 || $kingY == -1){
		echo json_encode(array("error" => "Could not find king (" . $kingName . ") in board" . json_encode($board)));
		exit();
	}

	//Check if king is checked
	$checkStatus = "none";
	if(isChecked($board, $color))
		$checkStatus = "check";

	if(sizeof($game["possibleMoves"]) == 0){
		if($checkStatus == "none")
			$checkStatus = "draw";
		if($checkStatus == "check")
			$checkStatus = "checkmate";
	}
	return $checkStatus;
}

//Checks if king is checked
function isChecked(&$board, $color){
	if(DEBUG_INFO)
		er("isChecked()");
	//Find the king
	$kingX = -1;
	$kingY = -1;
	$kingName = $color . "K";
	for($y = 0; $y < 8 && $kingX == -1; $y++){
		for($x = 0; $x < 8 && $kingX == -1; $x++){
			if($board[$y][$x] == $kingName){
				$kingX = $x;
				$kingY = $y;
			}
		}
	}
	if($kingX == -1)
		return false;

	//Set oponent color and piece names
	$oponentColor = "V";
	$pawnDir = 1;
	if($color == "V"){
		$oponentColor = "S";
		$pawnDir = -1;
	}
	$rookName = $oponentColor . "T";
	$knightName = $oponentColor . "H";
	$bishopName = $oponentColor . "L";
	$queenName = $oponentColor . "D";
	$kingName = $oponentColor . "K";
	$pawnName = $oponentColor . "B";

	//Check for dangerous pieces in different directions
	//Straight
	if(isPieceInDir($board, $kingX, $kingY, 0, 1, array($rookName, $queenName), array($kingName)))
		return true;
	if(isPieceInDir($board, $kingX, $kingY, 0, -1, array($rookName, $queenName), array($kingName)))
		return true;
	if(isPieceInDir($board, $kingX, $kingY, 1, 0, array($rookName, $queenName), array($kingName)))
		return true;
	if(isPieceInDir($board, $kingX, $kingY, -1, 0, array($rookName, $queenName), array($kingName)))
		return true;
	//Diagonals
	if(isPieceInDir($board, $kingX, $kingY, -1, -1 * $pawnDir, array($bishopName, $queenName), array($kingName, $pawnName)))
		return true;
	if(isPieceInDir($board, $kingX, $kingY, 1, -1 * $pawnDir, array($bishopName, $queenName), array($kingName, $pawnName)))
		return true;
	if(isPieceInDir($board, $kingX, $kingY, -1, 1 * $pawnDir, array($bishopName, $queenName), array($kingName)))
		return true;
	if(isPieceInDir($board, $kingX, $kingY, 1, 1 * $pawnDir, array($bishopName, $queenName), array($kingName)))
		return true;
	//Knights
	if(inBounds($kingX + 1, $kingY + 2) && $board[$kingY + 2][$kingX + 1] == $knightName)
		return true;
	if(inBounds($kingX - 1, $kingY + 2) && $board[$kingY + 2][$kingX - 1] == $knightName)
		return true;
	if(inBounds($kingX + 1, $kingY - 2) && $board[$kingY - 2][$kingX + 1] == $knightName)
		return true;
	if(inBounds($kingX - 1, $kingY - 2) && $board[$kingY - 2][$kingX - 1] == $knightName)
		return true;
	if(inBounds($kingX + 2, $kingY + 1) && $board[$kingY + 1][$kingX + 2] == $knightName)
		return true;
	if(inBounds($kingX + 2, $kingY - 1) && $board[$kingY - 1][$kingX + 2] == $knightName)
		return true;
	if(inBounds($kingX - 2, $kingY + 1) && $board[$kingY + 1][$kingX - 2] == $knightName)
		return true;
	if(inBounds($kingX - 2, $kingY - 1) && $board[$kingY - 1][$kingX - 2] == $knightName)
		return true;
	return false;
}

function isPieceInDir(&$board, $x, $y, $dx, $dy, $dangers, $oneStepDangers){
	if(DEBUG_INFO)
		er("isPieceInDir()");
	if($dx == 0 && $dy ==0)
		return false;
	$x += $dx;
	$y += $dy;
	$firstSquare = true;
	while(inBounds($x,$y)){
		foreach($dangers as $danger){
			if($board[$y][$x] == $danger)
				return true;
		}
		if($firstSquare){
			foreach($oneStepDangers as $danger){
				if($board[$y][$x] == $danger)
					return true;
			}	
		}
		//Blocking piece, stop looking
		if($board[$y][$x] != "")
			return false;
		$firstSquare = false;

		$x += $dx;
		$y += $dy;
	}
}

//Get player color
function getChessPlayerColor($matchId){
	if(DEBUG_INFO)
		er("getChessPlayerColor()");
	$result = getChessPlayerColorAndID($matchId);
	return $result["color"];
}

//Get player color and IDs
function getChessPlayerColorAndID($matchId){
	if(DEBUG_INFO)
		er("getChessPlayerColorAndID()");
	$result["color"] = "";
	$result["id"] = -1;
	$result["oponentId"] = -1;
	if(!isset($_SESSION) OR !issetSession("id")){
		return $result;
	}

	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get players from database
	$stmt = ps($conn, "SELECT `player1ID`, `player2ID` FROM `tableName` WHERE `matchIndex` = ?", "chessMatchList");
	$stmt->bind_param("i", $matchId);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `player1ID`, `player2ID` FROM `tableName` WHERE `matchIndex` = ?`");
		exit();
	}
	
	//Check what color player is
	$data = $stmt->get_result();
	$stmt->close();
	$conn->close();
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
		if($row["player1ID"] == getSession("id")){
			$result["color"] = "white";
			$result["id"] = intval($row["player1ID"]);
			$result["oponentId"] = intval($row["player2ID"]);
			return $result;
		}
		if($row["player2ID"] == getSession("id")){
			$result["color"] = "black";
			$result["id"] = intval($row["player2ID"]);
			$result["oponentId"] = intval($row["player1ID"]);
			return $result;
		}
	}
	return $result;
}

?>