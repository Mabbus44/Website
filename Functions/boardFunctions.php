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


//Get safe stones
function getSafeStones($x, $y, $color, &$board, &$scoreBoard){
	if(DEBUG_INFO)
		er("getSafeStones()");
	$safeStones = [];
	$notEyeSquares = [];
	$eyeSquares = [];
	$tempEyeSquares = [];
	$checkID = 0;
	$checkID2 = 0;
	$unique = true;
	$isEye = false;
	$cX2 = 0;
	$cY2 = 0;
	//Check so coordinates is inside board and right color stone is in that spot
	if($x>=0 && $x<19 && $y>=0 && $y<19){
		if($board[$x][$y] == $color){
			$safeStones = [];
			$safeStones[0] = [$x, $y];
		}
		else{
			$safeStones = [];
		}
		$checkID = 0;
		//Check neighbours of all safe stones found
		while($checkID<count($safeStones)){
			for($iX=-1; $iX<2; $iX++){
				for($iY=-1; $iY<2; $iY++){
					$cX=$safeStones[$checkID][0]+$iX;
					$cY=$safeStones[$checkID][1]+$iY;
					//Check that neighbour is inside board, and is not itself
					if($cX>=0 && $cX<19 && $cY>=0 && $cY<19 && !($iX==0 && $iY==0)){
						//If it is a diagonal neighbour, check that both stones bewteen is not oponent stones
						if($iX != 0 && $iY != 0 && $board[$cX-$iX][$cY] == 1-$color && $board[$cX][$cY-$iY] == 1-$color){
						}
						else{
							//If friendly stone, expand safeStones
							if($board[$cX][$cY] == $color){
								$unique = true;
								for($i=0; $i<count($safeStones) && $unique; $i++){
									if($safeStones[$i] == [$cX, $cY]){
										$unique = false;
									}
								}
								if($unique){
									$safeStones[count($safeStones)] = [$cX, $cY];
								}
							}
							//If empty or oponent spot and not diagonal, check for eyes (stop checking when 2 eyes are found)
							if(($iX == 0 || $iY == 0) && $board[$cX][$cY] != $color){
								$unique = true;
								for($i=0; $i<count($notEyeSquares) && $unique; $i++){
									if($notEyeSquares[$i]==[$cX, $cY]){
										$unique = false;
									}
								}
								for($i=0; $i<count($eyeSquares) && $unique; $i++){
									for($i2=0; $i2<count($eyeSquares[$i]) && $unique; $i2++){
										if($eyeSquares[$i][$i2]==[$cX, $cY]){
											$unique = false;
										}
									}
								}
								//If spot is not already checked for eyes, check it
								$isEye = false;
								if($unique){
									$tempEyeSquares = [];
									$tempEyeSquares[0] = [$cX, $cY];
									$isEye = true;
									$checkID2 = 0;
									while($checkID2<count($tempEyeSquares) && $isEye){
										for($i=0; $i<4 && $isEye; $i++){
											if($i==0){
												$cX2 = $tempEyeSquares[$checkID2][0]-1;
												$cY2 = $tempEyeSquares[$checkID2][1];
											}
											if($i==1){
												$cX2 = $tempEyeSquares[$checkID2][0];
												$cY2 = $tempEyeSquares[$checkID2][1]-1;
											}
											if($i==2){
												$cX2 = $tempEyeSquares[$checkID2][0];
												$cY2 = $tempEyeSquares[$checkID2][1]+1;
											}
											if($i==3){
												$cX2 = $tempEyeSquares[$checkID2][0]+1;
												$cY2 = $tempEyeSquares[$checkID2][1];
											}
											if($cX2>=0 && $cX2<19 && $cY2>=0 && $cY2<19){
												if($board[$cX2][$cY2] != $color){
													$unique = true;
													for($i2=0; $i2<count($tempEyeSquares) && $unique; $i2++){
														if($tempEyeSquares[$i2]==[$cX2, $cY2]){
															$unique = false;
														}
													}
													if($unique){
														$tempEyeSquares[count($tempEyeSquares)] = [$cX2, $cY2];
														//En eye is defined as surrounded area of maximum size 25 (may contain oponent stones)
														if(count($tempEyeSquares)>25){
															$isEye = false;
														}
													}
												}
											}
										}
										$checkID2++;
									}
								}
								//Add new checked squares to checked squares array
								if($isEye){
									$eyeSquares[count($eyeSquares)] = $tempEyeSquares;
									$tempEyeSquares = [];
								}else{
									for($i=0; $i<count($tempEyeSquares); $i++){
										$notEyeSquares[count($notEyeSquares)] = $tempEyeSquares[$i];
									}
									$tempEyeSquares = [];
								}
							}
						}
					}
				}
			}
			$checkID++;
		}
	}
	return [$safeStones, $eyeSquares];
}


//Remove dead stones and count points
function countPoints($matchIndex)
{
	if(DEBUG_INFO)
		er("countPoints()");
	$board = getBoard($matchIndex);
	$board = $board["board"];
	$scoreBoard = [];
	$points = [];
	$points[0] = 0;
	$points[1] = 7.5;
	$safeSquares = [];
	$safeGroups = [];
	$cX = 0;
	$cY = 0;
	$color = -1;
	$isSafe = false;
	$isEye = false;
	$unique = true;
	$checkID = 0;
	//Set scoreBoard to -1
	for($x = 0; $x < 19; $x++){
		$scoreBoard[$x] = [];
		for($y = 0; $y < 19; $y++){
			$scoreBoard[$x][$y] = -1;
		}
	}
	//Get all groups of safe squares, and their eyes
	for($x = 0; $x < 19; $x++){
		for($y = 0; $y < 19; $y++){
			if($board[$x][$y]!=2 && $scoreBoard[$x][$y] == -1){
				$ret = getSafeStones($x, $y, $board[$x][$y], $board, $scoreBoard);
				if(count($ret[1])>1){
					$safeGroups[count($safeGroups)] = ["stones"=> $ret[0], "eyes"=> $ret[1]];
				}
				for($i=0; $i< count($ret[0]); $i++){
					$scoreBoard[$ret[0][$i][0]][$ret[0][$i][1]] = 2;
				}
			}
		}
	}
	//Reset all stones that where not safe to -1 so that they will be checked again in next for loop
	//(The reson they where set to 2 where to avoid them being checked multiple times in previous for loop)
	for($x = 0; $x < 19; $x++){
		for($y = 0; $y < 19; $y++){
			if($scoreBoard[$x][$y] == 2){
				$scoreBoard[$x][$y] = -1;
			}
		}
	}
	//Check all eyes of all safeGroups and disqualify the eyes that have another safe group inside them
	for($i = 0; $i < count($safeGroups); $i++){																												//Go through all safeGroups
		for($i2 = 0; $i2 < count($safeGroups[$i]["eyes"]); $i2++){																			//And all the eyes in that group
			$isEye = true;
			for($i3 = 0; $isEye && $i3 < count($safeGroups[$i]["eyes"][$i2]); $i3++){											//And all the squares in that eye
				if($board[$safeGroups[$i]["eyes"][$i2][$i3][0]][$safeGroups[$i]["eyes"][$i2][$i3][1]] != 2){//If that square has a stone on it...
					for($i4 = 0; $isEye && $i4 < count($safeGroups); $i4++){																	//Go through all safegroups
						if($i != $i4){																																					//Except itself
							for($i5 = 0; $isEye && $i5 < count($safeGroups[$i4]["stones"]); $i5++){								//And compare the square with all the stones
								if($safeGroups[$i]["eyes"][$i2][$i3]==$safeGroups[$i4]["stones"][$i5]){
									$isEye = false;
								}
							}
						}
					}
				}
			}
			if(!$isEye){																																									//If en stone inside an eye was found to be safe, delete the eye
				\array_splice($safeGroups[$i]["eyes"], $i2, 1);
				$i2--;
			}
		}
	}
	//Set safe scoreBoard squares
	for($i = 0; $i < count($safeGroups); $i++){
		if(count($safeGroups[$i]["eyes"]) > 1){
			$color = $board[$safeGroups[$i]["stones"][0][0]][$safeGroups[$i]["stones"][0][1]];
			for($i2 = 0; $i2 < count($safeGroups[$i]["stones"]); $i2++){
				$scoreBoard[$safeGroups[$i]["stones"][$i2][0]][$safeGroups[$i]["stones"][$i2][1]] = $color;
			}
		}
	}
	//Set squares surrounded by safe squares to also be safe
	for($x = 0; $x < 19; $x++){
		for($y = 0; $y < 19; $y++){
			if($scoreBoard[$x][$y] == -1){
				$safeSquares = [];
				$safeSquares[0] = [$x, $y];
				$checkID = 0;
				$color = -1;
				$isSafe = true;
				//Check neighbours of all safe squares found
				while($checkID<count($safeSquares)){
					for($i=0; $i<4; $i++){
						if($i==0){
							$cX = $safeSquares[$checkID][0]-1;
							$cY = $safeSquares[$checkID][1];
						}
						if($i==1){
							$cX = $safeSquares[$checkID][0];
							$cY = $safeSquares[$checkID][1]-1;
						}
						if($i==2){
							$cX = $safeSquares[$checkID][0];
							$cY = $safeSquares[$checkID][1]+1;
						}
						if($i==3){
							$cX = $safeSquares[$checkID][0]+1;
							$cY = $safeSquares[$checkID][1];
						}
						if($cX>=0 && $cX<19 && $cY>=0 && $cY<19){
							//If unchecked square, expand safeSquares
							if($scoreBoard[$cX][$cY] == -1){
								$unique = true;
								for($i2=0; $i2<count($safeSquares) && $unique; $i2++){
									if($safeSquares[$i2]==[$cX, $cY]){
										$unique = false;
									}
								}
								if($unique){
									$safeSquares[count($safeSquares)] = [$cX, $cY];
								}
							}
							else{
								//If checked square, if first color, save it. If different color, square is not safe
								if($color == -1 && $scoreBoard[$cX][$cY] != 2){
									$color = $scoreBoard[$cX][$cY];
								}
								else{
									if($scoreBoard[$cX][$cY] != $color){
										$isSafe = false;
									}
								}
							}
						}
					}
					$checkID++;
				}
				//Set safe value for squares just checked
				if($color == -1 || $isSafe == false){
					$color = 2;
				}
				for($i=0; $i<count($safeSquares); $i++){
					$scoreBoard[$safeSquares[$i][0]][$safeSquares[$i][1]] = $color;
				}
			}
		}
	}
	//Set score
	for($x = 0; $x < 19; $x++){
		for($y = 0; $y < 19; $y++){
			if($scoreBoard[$x][$y] == 0){
				$points[0]++;
			}else if($scoreBoard[$x][$y] == 1){
				$points[1]++;
			}else{
				if($board[$x][$y] == 0){
					$points[0]++;
				}else if($board[$x][$y] == 1){
					$points[1]++;
				}
			}				
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