<?php
function listOfChallanges(){
	include_once("accountFunctions.php");
	$servername = "rasmus.today.mysql";
	$username = "rasmus_today";
	$password = "9Nah5fEsDTayJ5doJVaXuAb6";
	$dbname = "rasmus_today";

	//Conect to database
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	if(!$conn){
		$result["error"] = "error: Database connection error --- " . $conn.error;
		echo json_encode($result);
		exit();
	}

	//Get users from database
	$stmt = $conn->prepare("SELECT `user1ID` FROM `challanges` WHERE `user2ID` = ?");
	$stmt->bind_param("i", $_SESSION["id"]);
	if(!$stmt->execute()){
		$result["error"] = "error: Could note execute prepared statement";
		echo json_encode($result);
		exit();
	}
	$result = $stmt->get_result();
	echo "<select size=\"" . $result->num_rows . "\" name=\"challanges\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . getNameFromID($row["user1ID"]) . "</option>";
		}
	}
	echo "</select>";
	$conn->close();
}

function listOfMatches(){
	include_once("accountFunctions.php");
	$servername = "rasmus.today.mysql";
	$username = "rasmus_today";
	$password = "9Nah5fEsDTayJ5doJVaXuAb6";
	$dbname = "rasmus_today";

	//Conect to database
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	if(!$conn){
		$result["error"] = "error: Database connection error --- " . $conn.error;
		echo json_encode($result);
		exit();
	}

	//Get matches from database
	$stmt = $conn->prepare("SELECT `matchIndex`,`player1ID`, `player2ID` FROM `matchList` WHERE `player1ID` = ? OR `player2ID` = ?");
	$stmt->bind_param("ii", $_SESSION["id"], $_SESSION["id"]);
	if(!$stmt->execute()){
		$result["error"] = "error: Could note execute prepared statement";
		echo json_encode($result);
		exit();
	}
	$result = $stmt->get_result();

	//Create oponent name list
	$matchIDs = array();
echo "<select size=\"" . $result->num_rows . "\" id=\"oponentName\" onchange=\"setSelect(this.selectedIndex)\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			if($row["player1ID"] == $_SESSION["id"]){
				echo "<option>" . getNameFromID($row["player2ID"]) . "</option>";
			}else{
				echo "<option>" . getNameFromID($row["player1ID"]) . "</option>";
			}
		}
	}
	echo "</select>";

	//Create match id list
	$result->data_seek(0);
	echo "<select size=\"" . $result->num_rows . "\" id=\"matchID\"  onchange=\"setSelect(this.selectedIndex)\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . $row["matchIndex"] . "</option>";
		}
	}
	echo "</select>";

	$conn->close();
}

function acceptChallange(){
	if($_POST){
		$servername = "rasmus.today.mysql";
		$username = "rasmus_today";
		$password = "9Nah5fEsDTayJ5doJVaXuAb6";
		$dbname = "rasmus_today";

		//Conect to database
		$conn = mysqli_connect($servername, $username, $password, $dbname);
		if(!$conn){
			$result["error"] = "error: Database connection error --- " . $conn.error;
			echo json_encode($result);
			exit();
		}

		//Get challanges from database
		$stmt = $conn->prepare("SELECT `user1ID`, `user2ID` FROM `challanges` WHERE `user2ID` = ?");
		$stmt->bind_param("i", $_SESSION["id"]);
		if(!$stmt->execute()){
			$result["error"] = "error: Could note execute prepared statement";
			echo json_encode($result);
			exit();
		}
		$challanges = $stmt->get_result();
		$stmt->close();
		
		//Reload page if no challange found
		if($challanges->num_rows < 1){
			header("Location: main.php");
			return 0;
		}
		
		//Delete challange
		$ID1 = 0;
		$ID2 = 0;
		while($row = $challanges->fetch_assoc()){
			if(getNameFromID($row["user1ID"]) == $_POST["challanges"]){
				$stmt = $conn->prepare("DELETE FROM `challanges` WHERE `user1ID` = " . $row["user1ID"] . " AND `user2ID` = " . $row["user2ID"]);
				if(!$stmt->execute()){
					$result["error"] = "error: Could note execute prepared statement";
					echo json_encode($result);
					exit();
				}
				$ID1 = $row["user1ID"];
				$ID2 = $row["user2ID"];
			}
		}
		$stmt->close();
		
		//Reload page if challange not found
		if($ID1 == 0){
			header("Location: main.php");
			return 0;
		}
		
		//Get free match index
		$stmt = $conn->prepare("SELECT `value` FROM `settings` WHERE `setting` = \"freeMatchID\"");
		if(!$stmt->execute()){
			$result["error"] = "error: Could note execute prepared statement";
			echo json_encode($result);
			exit();
		}
		$matchIndexRows = $stmt->get_result();
		$row = $matchIndexRows->fetch_assoc();
		$matchIndex = $row["value"];
		$stmt->close();

		//Create new game
		$stmt = $conn->prepare("INSERT INTO `matchList`(`matchIndex`, `player1ID`, `player2ID`) VALUES (?, ?, ?)");
		$stmt->bind_param("iii", $matchIndex, $ID1, $ID2);
		if(!$stmt->execute()){
			$result["error"] = "error: Could note execute prepared statement";
			echo json_encode($result);
			exit();
		}
		$stmt->close();
		
		//Increase matchIndex by one
		$matchIndex = $matchIndex + 1;
		$stmt = $conn->prepare("UPDATE `settings` SET `value`=(?) where `setting` = 'freeMatchID'");
		$stmt->bind_param("i", $matchIndex);
		if(!$stmt->execute()){
			$result["error"] = "error: Could note execute prepared statement";
			echo json_encode($result);
			exit();
		}
		$stmt->close();

		$conn->close();
		header("Location: main.php");
	}
}
?>