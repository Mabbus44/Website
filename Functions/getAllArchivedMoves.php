<?php
	// Returns board with index "matchIndex"
    include_once(__DIR__."/../Functions/commonFunctions.php");
    $matchIndex = $_REQUEST["matchIndex"];
	$conn = dbCon();
	if(!$conn)
		exit();
    
	//Read moves from database
	$stmt = ps($conn, "SELECT `x`, `y`, `action`, `moveIndex` FROM `tableName` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC", "archivedGames");
	$stmt->bind_param("i", $matchIndex);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `x`, `y`, `action`, `moveIndex` FROM `currentGames` WHERE `matchIndex` = ? ORDER BY `moveIndex` ASC`");
		exit();
	}
	
	//Build bord from moves and return board
	$moves = $stmt->get_result();
	$stmt->close();
	$rowNum = 0;
	$result["moves"] = array();
	$result["currMove"] = $moves->num_rows;
	$result["lastColor"] = 1-($moves->num_rows % 2);
	$result["lastAction"] = "playStone";
	if($moves->num_rows > 0) {
		while($row = $moves->fetch_assoc()) {
			if($rowNum == $moves->num_rows-1)
				$result["lastAction"] = $row["action"];
			$result["moves"][$rowNum] = $row;
			$rowNum = $rowNum + 1;
		}
	}
	$conn->close();
	echo json_encode($result);
?>