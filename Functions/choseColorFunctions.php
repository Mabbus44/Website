<?php
include_once(__DIR__."/../Functions/commonFunctions.php");

function handlePost(){
	if(DEBUG_INFO)
		er("handlePost()");
	if($_POST){
		//Save challenges id and reload
		if($_POST["challengerID"]){
			setSession("challengerID", $_POST["challengerID"]);
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/choseColor.php");
		}
		//If color was chosen, start game
		if(getSession("challengerID") and ($_POST["black"] or $_POST["white"])){
			$isWhite = false;
			if($_POST["black"]){
				setSession("color", "black");
			}
			if($_POST["white"]){
				setSession("color", "white");
				$isWhite = true;
			}

			//Conect to database
			$conn = dbCon();
			if(!$conn)
				exit();

			//Get challenges from database
			$stmt = ps($conn, "SELECT `user1ID`, `user2ID` FROM `tableName` WHERE `user2ID` = ?", "challenges");
			$sessionID = getSession("id");
			$stmt->bind_param("i", $sessionID);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID`, `user2ID` FROM `challenges` WHERE `user2ID` = ?`");
				exit();
			}
			$challenges = $stmt->get_result();
			$stmt->close();
			
			//Reload page if no challenge found
			if($challenges->num_rows < 1){
				er("No challenged found in function handlePost in choseColorFunctions.php");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
				return 0;
			}
			
			//Delete challenge
			$ID1 = 0;
			$ID2 = 0;
			while($row = $challenges->fetch_assoc()){
				if($row["user1ID"] == getSession("challengerID")){
					$stmt = ps($conn, "DELETE FROM `tableName` WHERE `user1ID` = " . $row["user1ID"] . " AND `user2ID` = " . $row["user2ID"], "challenges");
					if(!$stmt->execute()){
						er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `DELETE FROM `challenges` WHERE `user1ID` = " . $row["user1ID"] . " AND `user2ID` = " . $row["user2ID"] . "`");
						exit();
					}
					$ID1 = $row["user1ID"];
					$ID2 = $row["user2ID"];
				}
			}
			$stmt->close();
			
			//Reload page if challenge not found
			if($ID1 == 0){
				er("No challenged found 2 in function handlePost in choseColorFunctions.php");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
				return 0;
			}
			
			//Swap players if player is white
			if(!$isWhite){
				$ID3 = $ID1;
				$ID1 = $ID2;
				$ID2 = $ID3;
			}
			
			//Create new game
			$stmt = ps($conn, "INSERT INTO `tableName`(`player1ID`, `player2ID`) VALUES (?, ?)", "matchList");
			$stmt->bind_param("ii", $ID1, $ID2);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `matchList`(`player1ID`, `player2ID`) VALUES (?, ?)`");
				exit();
			}
			$stmt->close();

			//Get match ID
			$stmt = ps($conn, "SELECT `matchIndex` FROM `tableName` WHERE `player1ID`=".$ID1." AND `player2ID`=".$ID2." AND `endCause` IS NULL", "matchList");
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " SELECT `matchIndex` FROM `tableName` WHERE `player1ID`=".$ID1." AND `player2ID`=".$ID2." AND `andCause` IS NULL");
				exit();
			}
			$dbResult = $stmt->get_result();
			if($dbResult->num_rows == 0){
				er("Didnt find newly created matchIndex in choseColorFunctions.php");
				exit();
			}
			if($dbResult->num_rows > 1){
				er("Got multiple hits for newly created matchIndex in choseColorFunctions.php");
				exit();
			}
			$row = $dbResult->fetch_assoc();
			$stmt->close();
			$conn->close();
			$matchIndex = $row["matchIndex"];
			
			//clear session variables;
			if(getSession("challengerID")){
				unsetSession("challengerID");
			}
			if(getSession("color")){
				unsetSession("color");
			}
			
			//Navigate home if white, to board if black.
			if($isWhite){
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
			}
			else{
				$url = "Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/board.php?id=" . $matchIndex;
				header($url);
			}
		}
	}
}
?>