<?php
	include_once(__DIR__."/../Functions/commonFunctions.php");
	session_start();
	setSession("language", $_POST["langID"]);
?>