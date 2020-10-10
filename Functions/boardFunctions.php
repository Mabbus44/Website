<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");

//Get player color
function getPlayerColor($gameID){
	if(DEBUG_INFO)
		er("getPlayerColor()");
	$result = getPlayerColorAndID($gameID);
	return $result["color"];
}

//Get player color and IDs
function getPlayerColorAndID($gameID){
	if(DEBUG_INFO)
		er("getPlayerColorAndID()");
	$result["color"] = 2;
	$result["id1"] = -1;
	$result["id2"] = -1;
	if(!isset($_SESSION) OR !issetSession("id")){
		return $result;
	}

	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get players from database
	$stmt = ps($conn, "SELECT `player1ID`, `player2ID` FROM `tableName` WHERE `matchIndex` = ? AND `winner` IS NULL", "matchList");
	$stmt->bind_param("i", $gameID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `player1ID`, `player2ID` FROM `matchList` WHERE `matchIndex` = ? AND `winner` IS NULL`");
		exit();
	}
	
	//Check what color player is
	$data = $stmt->get_result();
	$stmt->close();
	$conn->close();
	if($data->num_rows > 0){
		$row = $data->fetch_assoc();
		$result["id1"] = $row["player1ID"];
		$result["id2"] = $row["player2ID"];
		if($row["player1ID"] == getSession("id")){
			$result["color"] = 0;
			return $result;
		}
		if($row["player2ID"] == getSession("id")){
			$result["color"] = 1;
			return $result;
		}
	}
	$result["color"] = 2;
	return $result;
}

//Get surrounded area
function getSurroundedArea(&$board, $x, $y){
	if(DEBUG_INFO)
		er("getSurroundedArea()");
	$result["area"] = [];
	$result["color"] = 2;
	$result["surrounded"] = False;
	if($x<0 || $x>18 || $y<0 || $y>18){
		er("Invalid parameters in getSurroundedArea x(" . $x . ") y(" . $y . ")");
		return $result;
	}
	if($board[$x][$y] != 2){
		return $result;
	}
	$result["area"][0] = [$x, $y];
	$result["surrounded"] = True;
	$areaSize = 1;
	$checkID = 0;
	while($checkID<$areaSize){
		for($i=0; $i<4; $i++){
			if($i==0){
				$cX = $result["area"][$checkID][0]-1;
				$cY = $result["area"][$checkID][1];
			}
			if($i==1){
				$cX = $result["area"][$checkID][0];
				$cY = $result["area"][$checkID][1]-1;
			}
			if($i==2){
				$cX = $result["area"][$checkID][0];
				$cY = $result["area"][$checkID][1]+1;
			}
			if($i==3){
				$cX = $result["area"][$checkID][0]+1;
				$cY = $result["area"][$checkID][1];
			}
			if($cX>=0 && $cX<19 && $cY>=0 && $cY<19){
				if($board[$cX][$cY] == 2){
					$unique = true;
					for($i2=0; $i2<$areaSize && $unique; $i2++){
						if($result["area"][$i2] == [$cX, $cY]){
							$unique = false;
						}
					}
					if($unique){
						$result["area"][$areaSize] = [$cX, $cY];
						$areaSize++;
					}
				}
				if($board[$cX][$cY] == 0 || $board[$cX][$cY] == 1){
					if($result["color"] == 2){
						$result["color"] = $board[$cX][$cY];
					}
					elseif($board[$cX][$cY] != $result["color"]){
						$result["surrounded"] = False;
					}
				}
			}
		}
		$checkID++;
	}
	if($result["color"] == 2){
		$result["surrounded"] = False;
	}
	return $result;
}

//Capture surrounded areas
function countPoints($matchIndex)
{
	if(DEBUG_INFO)
		er("countPoints()");
	$board = getBoard($matchIndex);
	$board = $board["board"];
	$points = [];
	$points[0] = 0;
	$points[1] = 7.5;
	for($y=0; $y<19; $y++){
		for($x=0; $x<19; $x++){
			if($board[$x][$y] == 2){
				$area = getSurroundedArea($board, $x, $y);
				if($area["surrounded"]){
					for($i=0; $i<count($area["area"]); $i++){
						$board[$area["area"][$i][0]][$area["area"][$i][1]] = $area["color"];
					}
				}
				else{
					for($i=0; $i<count($area["area"]); $i++){
						$board[$area["area"][$i][0]][$area["area"][$i][1]] = 3;
					}
				}					
			}
			if($board[$x][$y] == 0)
				$points[0] = $points[0] + 1;
			if($board[$x][$y] == 1)
				$points[1] = $points[1] + 1;
		}
	}
	return $points;
}

//Get surrounded stones
function getSurroundedStones(&$board, $x, $y, $color){
	if(DEBUG_INFO)
		er("getSurroundedStones(".$x.", ".$y.", ".$color.")");
	$capStones = [];
	if($x>=0 && $x<19 && $y>=0 && $y<19){
		if($board[$x][$y] == $color){
			$capStones = [];
			$capStones[0] = [$x, $y];
			$capStonesSize = 1;
			$surrounded = true;
		}
		else{
			$capStones = [];
			$capStonesSize = 0;
			$surrounded = false;
		}
		$checkID = 0;
		while($checkID<$capStonesSize && $surrounded){
			for($i=0; $i<4 && $surrounded; $i++){
				if($i==0){
					$cX = $capStones[$checkID][0]-1;
					$cY = $capStones[$checkID][1];
				}
				if($i==1){
					$cX = $capStones[$checkID][0];
					$cY = $capStones[$checkID][1]-1;
				}
				if($i==2){
					$cX = $capStones[$checkID][0];
					$cY = $capStones[$checkID][1]+1;
				}
				if($i==3){
					$cX = $capStones[$checkID][0]+1;
					$cY = $capStones[$checkID][1];
				}
				if($cX>=0 && $cX<19 && $cY>=0 && $cY<19){
					if($board[$cX][$cY] == $color){
						$unique = true;
						for($i2=0; $i2<$capStonesSize && $unique; $i2++){
							if($capStones[$i2] == [$cX, $cY]){
								$unique = false;
							}
						}
						if($unique){
							$capStones[$capStonesSize] = [$cX, $cY];
							$capStonesSize++;
						}
					}
					if($board[$cX][$cY] == 2){
						$surrounded = false;
						$capStones = [];
					}
				}
			}
			$checkID++;
		}
	}
	return $capStones;
}

//Capture surrounded stones
function captureStones(&$board, $x, $y, $color)
{
	if(DEBUG_INFO)
		er("captureStones()");
	for($i=0; $i<4; $i++){
		if($i==0){
			$tX = $x-1;
			$tY = $y;
		}
		if($i==1){
			$tX = $x;
			$tY = $y-1;
		}
		if($i==2){
			$tX = $x;
			$tY = $y+1;
		}
		if($i==3){
			$tX = $x+1;
			$tY = $y;
		}
		$capStones = getSurroundedStones($board, $tX, $tY, 1-$color);
		if(count($capStones)>0){
			for($i2=0; $i2<count($capStones); $i2++){
				$board[$capStones[$i2][0]][$capStones[$i2][1]] = 2;
			}
		}
	}
	return;
}

//Checks if square is a valid move
function validSquare($board, $oldBoard, $x, $y, $color){
	if(DEBUG_INFO)
		er("validSquare(".$x.", ".$y.", ".$color.")");
	//Check if square is taken
	if($board[$x][$y] != 2){
		return false;
	}
	
	//Perform move
	$board[$x][$y] = $color;
	captureStones($board, $x, $y, $color);

	//Check for board repetition
	$ans = false;
	for($x2 = 0; $x2 < 19; $x2++){
		for($y2 = 0; $y2 < 19; $y2++){
			if($board[$x2][$y2] != $oldBoard[$x2][$y2]){
				$ans = true;
			}
		}
	}

	//check for self surround
	if(count(getSurroundedStones($board, $x, $y, $color))>0){
		$ans = false;
	}
	$board[$x][$y] = 2;
	return $ans;
}

//Gets board with existing connection
function getBoardExistingCon($conn, $matchIndex){
	if(DEBUG_INFO)
		er("getBoardExistingCon()");
	if(!$conn){
		return $result;
	}
	//Read gamestate from database
	$stmt = ps($conn, "SELECT `x`, `y`, `action`, `moveIndex` FROM `tableName` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC", "currentGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `x`, `y`, `action`, `moveIndex` FROM `currentGames` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC`");
		exit();
	}
	
	//Build bord from moves and return board
	$gameState = $stmt->get_result();
	$stmt->close();
	$result["board"] = array();
	$result["oldBoard"] = array();
	for($x = 0; $x < 19; $x++){
		$result["board"][$x] = array();
		$result["oldBoard"][$x] = array();
		for($y = 0; $y < 19; $y++){ 
			$result["board"][$x][$y] = 2;
			$result["oldBoard"][$x][$y] = 2;
		}
	}
	$rowNum = 0;
	$result["currMove"] = $gameState->num_rows;
	$result["lastColor"] = 1-($gameState->num_rows % 2);
	$result["lastAction"] = "playStone";
	if($gameState->num_rows > 0) {
		while($row = $gameState->fetch_assoc()) {
			$result["lastAction"] = $row["action"];
			if($row["action"] == "playStone") {
				$result["board"][$row["x"]][$row["y"]] = $rowNum % 2;
				captureStones($result["board"], $row["x"], $row["y"], $rowNum % 2);
			}
			$rowNum = $rowNum + 1;
			if($rowNum == $gameState->num_rows-1){
				for($x = 0; $x < 19; $x++){
					for($y = 0; $y < 19; $y++){
						$result["oldBoard"][$x][$y] = $result["board"][$x][$y];
					}
				}
			}
		}
	}
	return $result;
}

//Gets board from database
function getBoard($matchIndex){
	if(DEBUG_INFO)
		er("getBoard()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get board
	$result = getBoardExistingCon($conn, $matchIndex);
	$conn->close();
	return $result;
}

//Gets move from database
function getMove($matchIndex, $moveIndex){
	if(DEBUG_INFO)
		er("getMove()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Read move from database
	$stmt = ps($conn, "SELECT `x`, `y`, `action`, `moveIndex` FROM `tableName` WHERE `matchIndex` = ? AND `moveIndex` = ?", "currentGames");
	$stmt->bind_param("ii", $matchIndex, $moveIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . "SELECT `x`, `y`, `action`, `moveIndex` FROM `currentGames` WHERE `matchIndex` = ? AND `moveIndex` = ?");
		exit();
	}
	
	//Return move data
	$move = $stmt->get_result();
	$stmt->close();
	if($move->num_rows > 0) {
		$result = $move->fetch_assoc();
	}
	else{
		$result = -1;
	}
	$conn->close();
	return $result;
}

//End game
function endGame($matchIndex, $endCauseVal){
	if(DEBUG_INFO)
		er("endGame()");
	$colorAndId = getPlayerColorAndID($matchIndex);
	$color = $colorAndId["color"];

	//Check that parameters has correct values
	if(!($color>=0 && $color<=1)){
		er("Invalid color " . $color . " in function endGame");
		exit();
	}

	//Get winner ID
	$points1 = 0;
	$points2 = 0;
	if($endCauseVal == 1){
		$points = countPoints($matchIndex);
		$points1 = $points[0];
		$points2 = $points[1];
		if($points[0] > $points[1]){
			$winnerID = $colorAndId["id1"];
			$loserID = $colorAndId["id2"];
		}
		else{
			$winnerID = $colorAndId["id2"];
			$loserID = $colorAndId["id1"];
		}
	}
	elseif($endCauseVal == 2){
		if($color == 0){
			$winnerID = $colorAndId["id2"];
			$loserID = $colorAndId["id1"];
		}
		else{
			$winnerID = $colorAndId["id1"];
			$loserID = $colorAndId["id2"];
		}
	}
	else{
		er("Invalid $endCauseVal (" . $endCauseVal . ") in endGame");
		exit();
	}
	$result["winnerName"] = getNameFromID($winnerID);
	$result["loserName"] = getNameFromID($loserID);
	$result["player1Name"] = getNameFromID($colorAndId["id1"]);
	$result["player2Name"] = getNameFromID($colorAndId["id2"]);
	$result["points1"] = $points1;
	$result["points2"] = $points2;

	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Insert winner into database
	$stmt = ps($conn, "UPDATE `tableName` SET `winner`=?, `endCause`=?, `points1`=?, `points2`=? WHERE `matchIndex` = ?", "matchList");
	$stmt->bind_param("iiddi", $winnerID, $endCauseVal, $points1, $points2, $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `UPDATE `matchList` SET `winner`=?, `endCause`=? WHERE `matchIndex` = ?`");
		exit();
	}
	$stmt->close();

	//Move all moves to archivedGames
	$stmt = ps($conn, "INSERT INTO `tableName`(`x`, `y`, `action`, `moveIndex`, `matchIndex`) SELECT `x`, `y`, `action`, `moveIndex`, `matchIndex` FROM `anotherTable` WHERE `matchIndex` = ?", "archivedGames", "currentGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `archivedGames`(`x`, `y`, `action`, `moveIndex`, `matchIndex`) SELECT `x`, `y`, `action`, `moveIndex`, `matchIndex` FROM `currentGames` WHERE `matchIndex` = ?`");
		exit();
	}
	$stmt->close();
	$stmt = ps($conn, "DELETE FROM `tableName` WHERE `matchIndex`= ?", "currentGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `DELETE FROM `currentGames` WHERE `matchIndex`= ?`");
		exit();
	}
	$stmt->close();
	$conn->close();
	return $result;
}

//Get match results
function getMatchResults($matchIndex){
	if(DEBUG_INFO)
		er("getMatchResults()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get results from database
	$stmt = ps($conn, "SELECT `player1ID`, `player2ID`, `winner`, `endCause`, `points1`, `points2` FROM `tableName` WHERE `matchIndex` = ?", "matchList");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . "SELECT `player1ID`, `player2ID`, `winner`, `endCause`, `points1`, `points2` FROM `matchList` WHERE `matchIndex` = ?");
		exit();
	}
	
	//Return results
	$result = $stmt->get_result();
	$stmt->close();
	if($result->num_rows > 0) {
		$result = $result->fetch_assoc();
		if(is_null($result["endCause"])){
			$result = -1;
		}
	}
	else{
		$result = -1;
	}
	$conn->close();
	if($result != -1){
		$result["player1Name"] = getNameFromID($result["player1ID"]);
		$result["player2Name"] = getNameFromID($result["player2ID"]);
		$result["winnerName"] = getNameFromID($result["winner"]);
		if($result["winner"] == $result["player1ID"]){
			$result["loserName"] = getNameFromID($result["player2ID"]);
		}
		else{
			$result["loserName"] = getNameFromID($result["player1ID"]);
		}
		if($result["endCause"] == "pass"){
			$result["info"] = dictRet("Score winner", [$result["player1Name"], $result["player2Name"], $result["points1"], $result["points2"], $result["winnerName"]]);
		}
		if($result["endCause"] == "surrender"){
			$result["info"] = dictRet("Surrender winner", [$result["loserName"], $result["winnerName"]]);
		}
	}
	return $result;
}

//Gets all moves of a match
function getAllMoves($matchIndex){
	if(DEBUG_INFO)
		er("getAllMoves()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Read moves from database
	$stmt = ps($conn, "SELECT `x`, `y`, `action`, `moveIndex` FROM `tableName` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC", "currentGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `x`, `y`, `action`, `moveIndex` FROM `currentGames` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC`");
		exit();
	}
	
	//Build bord from moves and return board
	$moves = $stmt->get_result();
	$stmt->close();
	$rowNum = 0;
	$result["moves"] = array();
	$result["currMove"] = $moves->num_rows;
	$result["lastColor"] = 1-($moves->num_rows % 2);
	$result["lastAction"] = "playStone";
	if($moves->num_rows > 0) {
		while($row = $moves->fetch_assoc()) {
			if($rowNum == $moves->num_rows-1)
				$result["lastAction"] = $row["action"];
			$result["moves"][$rowNum] = $row;
			$rowNum = $rowNum + 1;
		}
	}
	$conn->close();
	return $result;
}
?>