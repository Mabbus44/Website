<?php
include_once(__DIR__."/secrets.php");
//Tells if real or debug database and session variables should be used.
define("DEBUG",False);
define("DEBUG_INFO",False);

//Connect to database
function dbCon(){
	$conn = mysqli_connect(DB_SERVER_NAME, DB_USER_NAME, DB_PASSWORD, DB_NAME);
	if(!$conn){
		er("Could not connect to database");
		return False;
	}
	return $conn;
}

//Prepare statement
function ps($conn, $statement, $tableName, $anotherTable = False){
	if(DEBUG)
		$tableName = "DEBUG_" . $tableName;
	$replacedStatement = str_replace("tableName", $tableName, $statement);
	if($anotherTable != False){
		if(DEBUG)
			$anotherTable = "DEBUG_" . $anotherTable;
		$replacedStatement = str_replace("anotherTable", $anotherTable, $replacedStatement);
	}
	return $conn->prepare($replacedStatement);
}

//Write errors to database
function er($errorText){
	$conn = dbCon();
	if(!$conn)
		exit();
	
	$errorText = $errorText . " ID " . getSession("id") . " Username " . getSession("username");
	if(strlen($errorText)>255)
		$errorText = substr($errorText, 0, 255);
	$stmt = ps($conn, "INSERT INTO `tableName` (message) VALUES (?)", "error");
	$stmt->bind_param("s", $errorText);
	$stmt->execute();
	$stmt->close();
	$conn->close();
}

//Outputs errors in html file
function outputErrors(){
	$conn = dbCon();
	if(!$conn)
		exit();
	$stmt = ps($conn, "SELECT `time`, `message` FROM `tableName` WHERE 1 ORDER BY `time` DESC", "error");
	$stmt->execute();
	$result = $stmt->get_result();
	echo "<table style=\"width:100%\">";
	echo "<tr>";
	echo "<th>Time</th>";
	echo "<th>Error</th>";
	echo "</tr>";
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "<tr>";
			echo "<td>".$row["time"]."</td>";
			echo "<td>".$row["message"]."</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
	$stmt->close();
	$conn->close();
	return 0;
}

//Pepper for password storing
function pepper(){
	return PEPPER;
}

//Functions to handle sessions
function getSession($attribute){
	if(DEBUG)
		$attribute = "DEBUG_" . $attribute;
	return isset($_SESSION[$attribute]) ? $_SESSION[$attribute] : null;
}

function setSession($attribute, $val){
	if(DEBUG)
		$attribute = "DEBUG_" . $attribute;
	$_SESSION[$attribute] = $val;
}

function issetSession($attribute){
	if(DEBUG)
		$attribute = "DEBUG_" . $attribute;
	return isset($_SESSION[$attribute]);
}

function unsetSession($attribute){
	if(DEBUG)
		$attribute = "DEBUG_" . $attribute;
	unset($_SESSION[$attribute]);
}

?>