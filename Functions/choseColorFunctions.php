<?php
include_once(__DIR__."/../Functions/commonFunctions.php");

function handlePost(){
	if($_POST){
		//Save challanges id and reload
		if($_POST["challangerID"]){
			setSession("challangerID", $_POST["challangerID"]);
			header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/choseColor.php");
		}
		//If color was chosen, start game
		if(getSession("challangerID") and ($_POST["black"] or $_POST["white"])){
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

			//Get challanges from database
			$stmt = ps($conn, "SELECT `user1ID`, `user2ID` FROM `tableName` WHERE `user2ID` = ?", "challanges");
			$sessionID = getSession("id");
			$stmt->bind_param("i", $sessionID);
			if(!$stmt->execute()){
				er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `user1ID`, `user2ID` FROM `challanges` WHERE `user2ID` = ?`");
				exit();
			}
			$challanges = $stmt->get_result();
			$stmt->close();
			
			//Reload page if no challange found
			if($challanges->num_rows < 1){
				er("No challanged found in function handlePost in choseColorFunctions.php");
				header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/main.php");
				return 0;
			}
			
			//Delete challange
			$ID1 = 0;
			$ID2 = 0;
			while($row = $challanges->fetch_assoc()){
				if($row["user1ID"] == getSession("challangerID")){
					$stmt = ps($conn, "DELETE FROM `tableName` WHERE `user1ID` = " . $row["user1ID"] . " AND `user2ID` = " . $row["user2ID"], "challanges");
					if(!$stmt->execute()){
						er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `DELETE FROM `challanges` WHERE `user1ID` = " . $row["user1ID"] . " AND `user2ID` = " . $row["user2ID"] . "`");
						exit();
					}
					$ID1 = $row["user1ID"];
					$ID2 = $row["user2ID"];
				}
			}
			$stmt->close();
			
			//Reload page if challange not found
			if($ID1 == 0){
				er("No challanged found 2 in function handlePost in choseColorFunctions.php");
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
			if(getSession("challangerID")){
				unsetSession("challangerID");
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