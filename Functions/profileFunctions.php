<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/accountFunctions.php");

function matchList(){
	if(DEBUG_INFO)
		er("matchList()");
	
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get challenged users from database
	$stmt = ps($conn, "SELECT `player1ID`, `player2ID`, `matchIndex`, `winner`, `endCause`, `points1`, `points2` FROM `tableName` WHERE `player1ID` = ? OR `player2ID` = ?", "matchList");
	$sessionID = getSession("id");
	$stmt->bind_param("ii", $sessionID, $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . "SELECT `player1ID`, `player2ID`, `matchIndex`, `winner`, `endCause`, `points1`, `points2` FROM `matchList` WHERE `player1ID` = ? OR `player2ID` = ? ORDER BY `matchIndex` ASC");
		exit();
	}
	$result = $stmt->get_result();

	//Create challenger name list
	$oponentName = "";
	$outcome = "";
	echo "<select size=\"" . $result->num_rows . "\" name=\"matchList\" id=\"matchList\">";
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if($row["player1ID"] == $sessionID){
				$oponentName = getNameFromID($row["player2ID"]);
			}else{
				$oponentName = getNameFromID($row["player1ID"]);
			}
			if(is_null($row["endCause"])){
				echo "<option>" . "Oponent: " . $oponentName . ". Match not ended</option>";
			}else{
				if($row["winner"] == $sessionID){
					$outcome = dictRet("Win");
				}else{
					$outcome = dictRet("Loss");
				}
				if($row["endCause"] == "pass"){
					echo "<option>" . $outcome . ". " . dictRet("Oponent") . ": " . $oponentName . ". " . dictRet("Score") . ": " . $row["points1"] . "-" . $row["points2"] . "</option>";
				}else{
					echo "<option>" . $outcome . ". " . dictRet("Oponent") . ": " . $oponentName . ". " . dictRet("Surrender") . "</option>";
				}
			}
		}
	}
	echo "</select>";

	$stmt->close();
	$conn->close();
}

?>