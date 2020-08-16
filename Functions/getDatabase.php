<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
if(DEBUG_INFO)
	er("getDatabase.php");

//Conect to database
$conn = dbCon();
if(!$conn)
	exit();

//Read from db (for debugging)
$stmt = ps($conn, "SELECT * FROM tableName", "credentials");
if(!$stmt->execute()){
	er("Prepared statement failed (" . $stmt->errno . ") " . $stmt->error . " `SELECT * FROM credentials`");
	exit();
}
$dbResult = $stmt->get_result();
if($dbResult->num_rows > 0) {
	while($row = $dbResult->fetch_assoc()) {
		echo json_encode(row);
	}
}
$stmt->close();
$conn->close();
?>