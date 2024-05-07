<?php
include_once(__DIR__."/../Functions/commonFunctions.php");

function challengePlayer(){
	if(DEBUG_INFO)
		er("challengePlayer()");
	if(isset($_POST)){
		if(isset($_POST["challengedID"])){
			//Conect to database
			$conn = dbCon();
			if(!$conn)
				exit();

			//Check if user is valid
			$stmt = ps($conn, "SELECT `username` FROM `tableName` WHERE `id` = ?", "credentials");
			$stmt->bind_param("i", $_POST["challengedID"]);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username` FROM `credentials` WHERE `id` = ?`");
				exit();
			}
			$result = $stmt->get_result();
			if($result->num_rows < 1 || getSession("id")==$_POST["challengedID"]){
				er("Invalid challengedID " . $_POST["challengedID"] . " in function challengePlayer");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challenge.php");
				exit();
			}
			$stmt->close();

			//Check if user is already challenged
			$stmt = ps($conn, "SELECT `user1ID` FROM `tableName` WHERE (`user1ID` = ? AND `user2ID` = ?) OR (`user1ID` = ? AND `user2ID` = ?)", "challenges");
			$sessionID = getSession("id");
			$stmt->bind_param("iiii", $sessionID, $_POST["challengedID"], $_POST["challengedID"], $sessionID);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID` FROM `challenges` WHERE (`user1ID` = ? AND `user2ID` = ?) OR (`user1ID` = ? AND `user2ID` = ?)`");
				exit();
			}
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				er("User with challengedID " . $_POST["challengedID"] . " already challenged in function challengePlayer");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challenge.php");
				exit();
			}
			$stmt->close();
			
			//Check if user is already in a game
			$stmt = ps($conn, "SELECT `player1ID` FROM `tableName` WHERE ((`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)) AND `endCause` IS NULL", "matchList");
			$stmt->bind_param("iiii", $sessionID, $_POST["challengedID"], $_POST["challengedID"], $sessionID);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `player1ID` FROM `matchList` WHERE (`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)`");
				exit();
			}
			$result = $stmt->get_result();
			if($result->num_rows > 0){
				er("User with challengedID " . $_POST["challengedID"] . " already in game in function challengePlayer");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challenge.php");
				exit();
			}
			$stmt->close();

			//challenge user
			$stmt = ps($conn, "INSERT INTO `tableName` (user1ID, user2ID) VALUES (?, ?)", "challenges");
			$stmt->bind_param("ii", $sessionID, $_POST["challengedID"]);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `challenges` (user1ID, user2ID) VALUES (?, ?)`");
				exit();
			}
			$stmt->close();
			$conn->close();
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challenge.php");
		}
		if(isset($_POST["alreadyChallengedID"])){
			//Conect to database
			$conn = dbCon();
			if(!$conn)
				exit();

			//Remove challenge
			$stmt = ps($conn, "DELETE FROM `tableName` WHERE `user1ID` = ? AND `user2ID` = ?", "challenges");
			$sessionID = getSession("id");
			$stmt->bind_param("ii", $sessionID, $_POST["alreadyChallengedID"]);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `DELETE FROM `challenges` WHERE `user1ID` = ? AND `user2ID` = ?`");
				exit();
			}
			$stmt->close();

			$conn->close();
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/challenge.php");
		}
	}
}

function listOfAllPlayers(){
	if(DEBUG_INFO)
		er("listOfAllPlayers()");
	//Get filterString
	if(isset($_POST["filterString"])){
		$filterString = "%".$_POST["filterString"]."%";
	}
	else{
		$filterString = "%";
	}
	
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get challenged users from database
	$stmt = ps($conn, "SELECT `user1ID`, `user2ID` FROM `tableName` WHERE `user1ID` = ? OR `user2ID` = ?", "challenges");
	$sessionID = getSession("id");
	$stmt->bind_param("ii", $sessionID, $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID`, `user2ID` FROM `challenges` WHERE `user1ID` = ? OR `user2ID` = ?`");
		exit();
	}
	$result = $stmt->get_result();

	//Find ids in the challenged player list
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
	$stmt = ps($conn, "SELECT `username`, `id` FROM `tableName` WHERE `username` LIKE ? LIMIT 30", "credentials");
	$stmt->bind_param("s", $filterString);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username`, `id` FROM `credentials` WHERE 1`");
		exit();
	}
	$result = $stmt->get_result();

	//Create challenger name list
	echo "<select size=\"" . $result->num_rows . "\" name=\"challengedName\" id=\"challengedName\" onchange=\"setSelect(this.selectedIndex)\">";
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

	//Create challenger id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" name=\"challengedID\" id=\"challengedID\" onchange=\"setSelect(this.selectedIndex)\" required style=\"display: none\">";
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

function listOfChallengedPlayers(){
	if(DEBUG_INFO)
		er("listOfChallengedPlayers()");
	include_once("accountFunctions.php");
	
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Get users from database
	$stmt = ps($conn, "SELECT `user2ID` FROM `tableName` WHERE `user1ID` = ?", "challenges");
	$sessionID = getSession("id");
	$stmt->bind_param("i", $sessionID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user2ID` FROM `challenges` WHERE `user1ID` = ?`");
		exit();
	}

	//Create challenged player name list
	$result = $stmt->get_result();
	echo "<select size=\"" . $result->num_rows . "\" name=\"alreadyChallenged\" id=\"alreadyChallenged\" onchange=\"setSelect2(this.selectedIndex)\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . getNameFromID($row["user2ID"]) . "</option>";
		}
	}
	echo "</select>";

	//Create challenged players id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" name=\"alreadyChallengedID\" id=\"alreadyChallengedID\" onchange=\"setSelect2(this.selectedIndex)\" required style=\"display: none\">";
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