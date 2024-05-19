<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
include_once(__DIR__."/../Functions/dictionary.php");

function checkIfLoggedIn(){
	session_start();
	if(DEBUG_INFO)
		er("checkIfLoggedIn()");
	if(issetSession("username")){
		return true;
	}
	echo header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/logIn.php");
	return false;
}

function getNameFromID($id){
	if(DEBUG_INFO)
		er("getNameFromID()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();

	//Find username
	$stmt = ps($conn, "SELECT `username`, `id` FROM `tableName` WHERE `id` = ?", "credentials");
	$stmt->bind_param("i", $id);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT `username`, `id` FROM `credentials` WHERE `id` = ?`");
		exit();
	}
	$result = $stmt->get_result();
	$stmt->close();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		return $row["username"];
	}
	$conn->close();
	return "";
}

function getNameFromMatchID($matchID, $color){
	if(DEBUG_INFO)
		er("getNameFromMatchID()");
	//Conect to database
	$conn = dbCon();
	if(!$conn)
		exit();
	//Find user ID
	if($color == 0){
		$stmt = ps($conn, "SELECT `username` FROM `tableName` WHERE `id` IN (SELECT `player1ID` FROM `anotherTable` WHERE `matchIndex` = ?)", "credentials", "matchList");
	}else{
		$stmt = ps($conn, "SELECT `username` FROM `tableName` WHERE `id` IN (SELECT `player2ID` FROM `anotherTable` WHERE `matchIndex` = ?)", "credentials", "matchList");
	}		
	$stmt->bind_param("i", $matchID);
	if(!$stmt->execute()){
		er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . "SELECT `player1ID` FROM `matchList` WHERE `matchIndex` = ?");
		exit();
	}
	$result = $stmt->get_result();
	$stmt->close();
	if($result->num_rows > 0){
		$row = $result->fetch_assoc();
		return $row["username"];
	}
	$conn->close();
	return "";
}

function topPanel(){
	echo "<div id=\"topPanel\">";
	echo	 "<div id=\"topCenterPanel\">";
	echo		 "<form id=\"goIconButtonForm\" action=\"../Pages/main.php\">";
	echo		 	 "<button type=\"submit\" id=\"goIconButton\">".dictRet("Go")."</button>";
	echo		 "</form>";
	echo		 "<button id=\"enLangButton\" type=\"button\" onclick=\"changeLanguage(0)\"></button>";
	echo		 "<button id=\"chLangButton\" type=\"button\" onclick=\"changeLanguage(1)\"></button>";
	if(issetSession("username")){
		echo		 "<div class=\"column\">";
		echo			 "<a href=\"../Pages/profile.php\">".getSession("username")."</a>";
		echo			 "<a href=\"../Functions/logOut.php\">".dictRet("Log out")."</a>";
		echo		 "</div>";
	}
	else{
		echo		 "<a href=\"../Pages/logIn.php\">".dictRet("Log in")."</a>";
	}
	echo	 "</div>";
	echo "</div>";
}

function chessTopPanel(){
	echo "<div id=\"topPanel\">";
	echo	 "<div id=\"topCenterPanel\">";
	echo		 "<form id=\"goIconButtonForm\" action=\"../Pages/chessBoard.php\">";
	echo		 	 "<button type=\"submit\" id=\"goIconButton\">".dictRet("Chess")."</button>";
	echo		 "</form>";
	echo		 "<button id=\"enLangButton\" type=\"button\" onclick=\"changeLanguage(0)\"></button>";
	echo		 "<button id=\"chLangButton\" type=\"button\" onclick=\"changeLanguage(1)\"></button>";
	if(issetSession("username")){
		echo		 "<div class=\"column\">";
		echo			 "<a href=\"../Pages/profile.php\">".getSession("username")."</a>";
		echo			 "<a href=\"../Functions/logOut.php\">".dictRet("Log out")."</a>";
		echo		 "</div>";
	}
	else{
		echo		 "<a href=\"../Pages/logIn.php\">".dictRet("Log in")."</a>";
	}
	echo	 "</div>";
	echo "</div>";
}
?>