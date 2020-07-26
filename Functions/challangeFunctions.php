<?php
include_once(__DIR__."/../Functions/commonFunctions.php");

function challangePlayer(){
	if(isset($_POST)){
		if(isset($_POST["challangedID"])){
			//Conect to database
			$conn = dbCon();
			if(!$conn)
				exit();

			//Check if user is valid
			$stmt = ps($conn, "SELECT `username` FROM `tableName` WHERE `id` = ?", "credentials");
			$stmt->bind_param("i", $_POST["challangedID"]);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username` FROM `credentials` WHERE `id` = ?`");
				exit();
			}
			$result = $stmt->get_result();
			if($result->num_rows < 1 || getSession("id")==$_POST["challangedID"]){
				er("Invalid challangedID " . $_POST["challangedID"] . " in function challangePlayer");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challange.php");
				exit();
			}
			$stmt->close();

			//Check if user is already challanged
			$stmt = ps($conn, "SELECT `user1ID` FROM `tableName` WHERE (`user1ID` = ? AND `user2ID` = ?) OR (`user1ID` = ? AND `user2ID` = ?)", "challanges");
			$sessionID = getSession("id");
			$stmt->bind_param("iiii", $sessionID, $_POST["challangedID"], $_POST["challangedID"], $sessionID);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID` FROM `challanges` WHERE (`user1ID` = ? AND `user2ID` = ?) OR (`user1ID` = ? AND `user2ID` = ?)`");
				exit();
			}
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				er("User with challangedID " . $_POST["challangedID"] . " already challanged in function challangePlayer");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challange.php");
				exit();
			}
			$stmt->close();
			
			//Check if user is already in a game
			$stmt = ps($conn, "SELECT `player1ID` FROM `tableName` WHERE ((`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)) AND `endCause` IS NULL", "matchList");
			$stmt->bind_param("iiii", $sessionID, $_POST["challangedID"], $_POST["challangedID"], $sessionID);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `player1ID` FROM `matchList` WHERE (`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)`");
				exit();
			}
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				er("User with challangedID " . $_POST["challangedID"] . " already in game in function challangePlayer");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challange.php");
				exit();
			}
			$stmt->close();

			//Challange user
			$stmt = ps($conn, "INSERT INTO `tableName` (user1ID, user2ID) VALUES (?, ?)", "challanges");
			$stmt->bind_param("ii", $sessionID, $_POST["challangedID"]);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `challanges` (user1ID, user2ID) VALUES (?, ?)`");
				exit();
			}
			$stmt->close();
			$conn->close();
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challange.php");
		}
		if(isset($_POST["alreadyChallangedID"])){
			//Conect to database
			$conn = dbCon();
			if(!$conn)
				exit();

			//Remove challange
			$stmt = ps($conn, "DELETE FROM `tableName` WHERE `user1ID` = ? AND `user2ID` = ?", "challanges");
			$sessionID = getSession("id");
			$stmt->bind_param("ii", $sessionID, $_POST["alreadyChallangedID"]);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `DELETE FROM `challanges` WHERE `user1ID` = ? AND `user2ID` = ?`");
				exit();
			}
			$stmt->close();

			$conn->close();
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challange.php");
		}
	}
}

function listOfAllPlayers(){
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get challanged users from database
	$stmt = ps($conn, "SELECT `user1ID`, `user2ID` FROM `tableName` WHERE `user1ID` = ? OR `user2ID` = ?", "challanges");
	$sessionID = getSession("id");
	$stmt->bind_param("ii", $sessionID, $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID`, `user2ID` FROM `challanges` WHERE `user1ID` = ? OR `user2ID` = ?`");
		exit();
	}
	$result = $stmt->get_result();

	//Find ids in the challanged player list
	$unavailablePlayers = [];
	$unavailablePlayers[0] = $sessionID;
	$unavailablePlayerCount = 1;
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if($row["user1ID"] == $sessionID){
				$unavailablePlayers[$unavailablePlayerCount] = $row["user2ID"];
				$unavailablePlayerCount = $unavailablePlayerCount + 1;
			}
			if($row["user2ID"] == $sessionID){
				$unavailablePlayers[$unavailablePlayerCount] = $row["user1ID"];
				$unavailablePlayerCount = $unavailablePlayerCount + 1;
			}
		}
	}
	$stmt->close();
	
	//Check if user is already in a game
	$stmt = ps($conn, "SELECT `player1ID`, `player2ID` FROM `tableName` WHERE (`player1ID` = ? OR `player2ID` = ?) AND `endCause` IS NULL", "matchList");
	$stmt->bind_param("ii", $sessionID, $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `player1ID`, `player2ID` FROM `matchList` WHERE `player1ID` = ? OR `player2ID` = ?`");
		exit();
	}
	$result = $stmt->get_result();

	//Find ids in the playing player list
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if($row["player1ID"] == $sessionID){
				$unavailablePlayers[$unavailablePlayerCount] = $row["player2ID"];
				$unavailablePlayerCount = $unavailablePlayerCount + 1;
			}
			if($row["player2ID"] == $sessionID){
				$unavailablePlayers[$unavailablePlayerCount] = $row["player1ID"];
				$unavailablePlayerCount = $unavailablePlayerCount + 1;
			}
		}
	}
	$stmt->close();

	//Get users from database
	$stmt = ps($conn, "SELECT `username`, `id` FROM `tableName` WHERE 1", "credentials");
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username`, `id` FROM `credentials` WHERE 1`");
		exit();
	}
	$result = $stmt->get_result();

	//Create challanger name list
	echo "<select size=\"" . $result->num_rows . "\" name=\"challangedName\" id=\"challangedName\" onchange=\"setSelect(this.selectedIndex)\">";
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$ok = true;
			for($i=0; $i<$unavailablePlayerCount; $i++){
				if($unavailablePlayers[$i] == $row["id"]){
					$ok = false;
				}
			}
			if($ok){
				echo "<option>" . $row["username"] . "</option>";
			}
		}
	}
	echo "</select>";

	//Create challanger id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" name=\"challangedID\" id=\"challangedID\" onchange=\"setSelect(this.selectedIndex)\" required>";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$ok = true;
			for($i=0; $i<$unavailablePlayerCount; $i++){
				if($unavailablePlayers[$i] == $row["id"]){
					$ok = false;
				}
			}
			if($ok){
				echo "<option>" . $row["id"] . "</option>";
			}
		}
	}
	echo "</select>";
	$stmt->close();
	$conn->close();
}

function listOfChallangedPlayers(){
	include_once("accountFunctions.php");
	
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get users from database
	$stmt = ps($conn, "SELECT `user2ID` FROM `tableName` WHERE `user1ID` = ?", "challanges");
	$sessionID = getSession("id");
	$stmt->bind_param("i", $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user2ID` FROM `challanges` WHERE `user1ID` = ?`");
		exit();
	}

	//Create challanged player name list
	$result = $stmt->get_result();
	echo "<select size=\"" . $result->num_rows . "\" name=\"alreadyChallanged\" id=\"alreadyChallanged\" onchange=\"setSelect2(this.selectedIndex)\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . getNameFromID($row["user2ID"]) . "</option>";
		}
	}
	echo "</select>";

	//Create challanged players id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" name=\"alreadyChallangedID\" id=\"alreadyChallangedID\" onchange=\"setSelect2(this.selectedIndex)\" required>";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . $row["user2ID"] . "</option>";
		}
	}
	echo "</select>";
	$stmt->close();

	$conn->close();
}
?>