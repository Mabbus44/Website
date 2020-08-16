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
	echo "<select size=\"" . $result->num_rows . "\" id=\"oponentName\" onchange=\"setSelect(this.selectedIndex)\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			if($row["player1ID"] == $sessionID){
				echo "<option>" . getNameFromID($row["player2ID"]) . "</option>";
			}else{
				echo "<option>" . getNameFromID($row["player1ID"]) . "</option>";
			}
		}
	}
	echo "</select>";

	//Create match id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" id=\"matchID\"  onchange=\"setSelect(this.selectedIndex)\" style=\"display: none\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . $row["matchIndex"] . "</option>";
		}
	}
	echo "</select>";

	$conn->close();
}
?>