<?php
header('Cache-Control: no-cache');
session_start();
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/chessBoardFunctions.php");

//Set oponentID and color
$oponentId = getChessNemesis();
$color = "white";
if($oponentId == null && isset($_POST) && isset($_POST["oponentID"]))
	$oponentId = $_POST["oponentId"];
if(isset($_POST) && isset($_POST["color"]) && $_POST["color"] == "black")
	$color = "black";

//Check if oponent id is valid
if($oponentId == null || getSession("id")==$oponentId){
	er("oponentID is invalid in startChessGame.php");
	exit();
}

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Check if oponent is valid
$stmt = ps($conn, "SELECT `username` FROM `tableName` WHERE `id` = ?", "credentials");
$stmt->bind_param("i", $oponentId);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username` FROM `credentials` WHERE `id` = ?`");
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows < 1){
	er("OponentID " . $oponentId . " does not exist in startChessGame.php");
	exit();
}
$stmt->close();

//Check if users is already in a game
$stmt = ps($conn, "SELECT `matchIndex` FROM `tableName` WHERE ((`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)) AND `endCause` IS NULL", "chessMatchList");
$stmt->bind_param("iiii", getSession("id"), $oponentId, $oponentId, getSession("id"));
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `player1ID` FROM `chessMatchList` WHERE (`player1ID` = ? AND `player2ID` = ?) OR (`player1ID` = ? AND `player2ID` = ?)`");
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows > 0){
	//Navigate to game
	$row = $dbResult->fetch_assoc();
	$stmt->close();
	$conn->close();
	$matchIndex = $row["matchIndex"];
	echo json_encode(array("id" => $matchIndex));
	exit();
}
$stmt->close();

//Create new game
$stmt = ps($conn, "INSERT INTO `tableName`(`player1ID`, `player2ID`) VALUES (?, ?)", "chessMatchList");
if($color == "black"){
	$ID1 = $oponentId;
	$ID2 = getSession("id");
}else{
	$ID1 = getSession("id");
	$ID2 = $oponentId;
}	
$stmt->bind_param("ii", $ID1, $ID2);
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `INSERT INTO `chessMatchList`(`player1ID`, `player2ID`) VALUES (?, ?)`");
	exit();
}
$stmt->close();

//Get match ID
$stmt = ps($conn, "SELECT `matchIndex` FROM `tableName` WHERE `player1ID`=".$ID1." AND `player2ID`=".$ID2." AND `endCause` IS NULL", "chessMatchList");
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " SELECT `matchIndex` FROM `tableName` WHERE `player1ID`=".$ID1." AND `player2ID`=".$ID2." AND `andCause` IS NULL");
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows == 0){
	er("Didnt find newly created matchIndex in startChessGame.php");
	exit();
}
if($dbResult->num_rows > 1){
	er("Got multiple hits for newly created matchIndex in startChessGame.php");
	exit();
}
$row = $dbResult->fetch_assoc();
$stmt->close();
$conn->close();
$matchIndex = $row["matchIndex"];

//Navigate to game

echo json_encode(array("id" => $matchIndex));
?>