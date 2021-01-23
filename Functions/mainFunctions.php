<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");

function listOfChallenges(){
	if(DEBUG_INFO)
		er("listOfChallenges()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get users from database
	$stmt = ps($conn, "SELECT `user1ID` FROM `tableName` WHERE `user2ID` = ?", "challenges");
	$sessionID = getSession("id");
	$stmt->bind_param("i", $sessionID);
	if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID` FROM `challenges` WHERE `user2ID` = ?`");
		exit();
	}
	
	//Create challenger name list
	$result = $stmt->get_result();
	$stmt->close();
	echo "<select size=\"" . $result->num_rows . "\" name=\"challengerName\" id=\"challengerName\" onchange=\"setSelect2(this.selectedIndex)\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . getNameFromID($row["user1ID"]) . "</option>";
		}
	}
	echo "</select>";

	//Create challenger id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" name=\"challengerID\" id=\"challengerID\" onchange=\"setSelect2(this.selectedIndex)\" required style=\"display: none\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . $row["user1ID"] . "</option>";
		}
	}
	echo "</select>";
	$conn->close();
}

function listOfMatches(){
	if(DEBUG_INFO)
		er("listOfMatches()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get matches from database
	$stmt = ps($conn, "SELECT `matchIndex`,`player1ID`, `player2ID` FROM `tableName` WHERE (`player1ID` = ? OR `player2ID` = ?) AND `endCause` IS NULL", "matchList");
	$sessionID = getSession("id");
	$stmt->bind_param("ii", $sessionID, $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " SELECT `matchIndex`,`player1ID`, `player2ID` FROM `matchList` WHERE (`player1ID` = ? OR `player2ID` = ?) AND `endCause` IS NULL");
		exit();
	}
	$result = $stmt->get_result();
	$stmt->close();

	//Create oponent name list  
	if($result->num_rows > 0){
		$options = [];
		while($row = $result->fetch_assoc()){
			//Populate array
			if($row["player1ID"] == $sessionID){
				$options[] = array(isItMyTurn($row["matchIndex"], 0), getNameFromID($row["player2ID"]), $row["matchIndex"]);
			}else{
				$options[] = array(isItMyTurn($row["matchIndex"], 1), getNameFromID($row["player1ID"]), $row["matchIndex"]);
			}
		}
		//Sort array
		for($i=0; $i<count($options); $i++){
			if($options[$i][0]){
				for($i2=$i; $i2>0; $i2--){
					er("Before: " . $i . " = [" . $options[$i][0] . ", " . $options[$i][1] . ", " . $options[$i][2] . "]");
					er("Before: " . $i2 . " = [" . $options[$i2][0] . ", " . $options[$i2][1] . ", " . $options[$i2][2] . "]");
					$temp = $options[$i];
					$options[$i] = $options[$i2-1];
					$options[$i2-1] = $temp;
					er("After: " . $i . " = [" . $options[$i][0] . ", " . $options[$i][1] . ", " . $options[$i][2] . "]");
					er("After: " . $i2 . " = [" . $options[$i2][0] . ", " . $options[$i2][1] . ", " . $options[$i2][2] . "]");
				}
			}
		}
		//Echo options
		if(count($options) == 1 && $options[0][0]){
			echo "<select class=\"listWithSingleHighlight\" size=\"" . $result->num_rows . "\" id=\"oponentName\" onchange=\"setSelect(this.selectedIndex)\">";
		}else{
			echo "<select class=\"listWithHighlights\" size=\"" . $result->num_rows . "\" id=\"oponentName\" onchange=\"setSelect(this.selectedIndex)\">";
		}
		for($i=0; $i<count($options); $i++){
			//er("i: " . $i . " o[0]: " . $options[$i][0] . " o[1]: " . $options[$i][1] . " o[2]: " . $options[$i][2]);
			if($options[$i][0]){
				echo "<option class=\"highlighted\">! " . $options[$i][1] . "</option>";
			}else{
				echo "<option>" . $options[$i][1] . "</option>";
			}
		}
	}else{
		echo "<select class=\"listWithHighlights\" size=\"" . $result->num_rows . "\" id=\"oponentName\" onchange=\"setSelect(this.selectedIndex)\">";
	}		
	echo "</select>";

	//Create match id list
	echo "<select size=\"" . $result->num_rows . "\" id=\"matchID\"  onchange=\"setSelect(this.selectedIndex)\" style=\"display: none\">";
	if($result->num_rows > 0){
		for($i=0; $i<count($options); $i++){
			echo "<option>" . $options[$i][2] . "</option>";
		}
	}
	echo "</select>";

	$conn->close();
}

//Return true if it is "colors" turn
function isItMyTurn($matchIndex, $color){
	if(DEBUG_INFO)
		er("isItMyTurn()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Read moves from database
	$stmt = ps($conn, "SELECT `moveIndex` FROM `tableName` WHERE `matchIndex` = ?", "currentGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `moveIndex` FROM `currentGames` WHERE `matchIndex` = ?`");
		exit();
	}
	
	//If moveCount is even, its blacks turn
	$moves = $stmt->get_result();
	$stmt->close();
	$result = False;
	if($moves->num_rows % 2 == $color){
		$result = True;
	}
	$conn->close();
	return $result;
}
?>