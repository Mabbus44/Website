<?php
//Get surrounded stones
function getSurroundedStones(&$board, $x, $y, $color){
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
function validSquare(&$board, $x, $y, $color){
	if($board[$x][$y] != 2){
		return false;
	}
	$board[$x][$y] = $color;
	if(count(getSurroundedStones($board, $x, $y, $color))>0){
		$ans = false;
	}
	else{
		$ans = true;
	}
	$board[$x][$y] = 2;
	return $ans;
}

//Checks if color has any valid move
function validColor(&$board, $color){
	for($x=0; $x<19; $x++){
		for($y=0; $y<19; $y++){
			if(validSquare($board, $x, $y, $color)){
				return true;
			}
		}
	}
	return false;
}

//Gets board with existing connection
function getBoardExistingCon($conn, $matchIndex){
	if(!$conn){
		$result["error"] = "error: Database connection error --- " . $conn.error;
		return $result;
	}
	//Read gamestate from database
	$query = "SELECT * FROM currentGames WHERE `matchIndex` = " . $matchIndex;
	$gameState = $conn->query($query);
	if($conn->errno){
		$result["error"] = "error: Could not select from database --- " . $query . " --- " . $conn->error;
		return $result;
	}
	$result["board"] = array();
	for($x = 0; $x < 19; $x++){
		$result["board"][$x] = array();
		for($y = 0; $y < 19; $y++){ 
			$result["board"][$x][$y] = 2;
		}
	}
	$result["lastColor"] = 1;
	$result["currMove"] = 0;
	if($gameState->num_rows > 0) {
		while($row = $gameState->fetch_assoc()) {
			$result["board"][$row["x"]][$row["y"]] = $row["color"];
			captureStones($result["board"], $row["x"], $row["y"], $row["color"]);
			if($row["moveIndex"]>=$result["currMove"]){
				$result["currMove"] = $row["moveIndex"]+1;
				$result["lastColor"] = $row["color"];
			}
		}
	}
	$result["info"] = "info: success";
	return $result;
}

//Gets board from database
function getBoard($matchIndex){
	$servername = "rasmus.today.mysql";
	$username = "rasmus_today";
	$password = "9Nah5fEsDTayJ5doJVaXuAb6";
	$dbname = "rasmus_today";

	//Conect to database
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	$result = getBoardExistingCon($conn, $matchIndex);
	mysqli_close($conn);
	return $result;
}
?>