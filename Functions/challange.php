<?php
function challangePlayer(){
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

		//Check if users are valid
		$query = "SELECT `username`, `id` FROM `credentials` WHERE 1";
		$users = $conn->query($query);
		$id1 = $_SESSION["id"];
		$id2 = -1;
		if($users->num_rows > 0) {
			while($row = $users->fetch_assoc()) {
				if(strcmp($row["username"], $_POST["challangedPlayer"]) == 0){
					$id2 = $row["id"];
				}
			}
		}
		if($id1<1 || $id2<1 || $id1==$id2){
			header("Location: challange.php");
			exit();
		}

		//Check if user is already challanged
		$query = "SELECT * FROM `challanges` WHERE `user1ID` = " . $id1 . " AND `user2ID` = " . $id2;
		$users = $conn->query($query);
		if($users->num_rows > 0) {
			header("Location: challange.php");
			exit();
		}
		
		//Check if users are challanging eachother or if they are already playing a game
		$query = "SELECT * FROM `matchList` WHERE `player1ID` = " . $id1 . " AND `player2ID` = " . $id2;
		$matches = $conn->query($query);
		$matchExist = $users->num_rows;
		$query = "SELECT * FROM `matchList` WHERE `player1ID` = " . $id2 . " AND `player2ID` = " . $id1;
		$matches = $conn->query($query);
		$matchExist = $matchExist + $users->num_rows;
		$query = "SELECT * FROM `challanges` WHERE `user1ID` = " . $id2 . " AND `user2ID` = " . $id1;
		$users = $conn->query($query);
		if($users->num_rows > 0 && $matchExist == 0) {
			//Start game
		}
		if($users->num_rows > 0 || $matchExist > 0) {
			header("Location: challange.php");
			exit();
		}

		//Challange user
		$stmt = $conn->prepare("INSERT INTO challanges (user1ID, user2ID) VALUES (?, ?)");
		$stmt->bind_param("ii", $id1, $id2);
		if(!$stmt->execute()){
			$result["error"] = "error: Could not insert credentials into database ";
			mysqli_close($conn);
			echo json_encode($result);
			exit();
		}
		$stmt->close();
		$conn->close();
		header("Location: challange.php");
	}
}

function listOfAllPlayers(){
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
	$query = "SELECT `username` FROM `credentials` WHERE 1";
	$usernames = $conn->query($query);
	echo "<select size=\"" . $usernames->num_rows . "\" name=\"challangedPlayer\" required>";
	if($usernames->num_rows > 0) {
		while($row = $usernames->fetch_assoc()) {
			echo "<option>" . $row["username"] . "</option>";
		}
	}
	echo "</select>";
	$conn->close();
}

function listOfChallangedPlayers(){
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
	$stmt = $conn->prepare("SELECT `user2ID` FROM `challanges` WHERE `user1ID` = ?");
	$stmt->bind_param("i", $_SESSION["id"]);
	if(!$stmt->execute()){
		$result["error"] = "error: Could note execute prepared statement";
		echo json_encode($result);
		exit();
	}
	$result = $stmt->get_result();
	echo "<select size=\"" . $result->num_rows . "\" name=\"alreadyChallanged\">";
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			echo "<option>" . getNameFromID($row["user2ID"]) . "</option>";
		}
	}
	echo "</select>";
	$conn->close();
}
?>