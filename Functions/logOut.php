<?php
include_once(__DIR__."/../Functions/commonFunctions.php");
session_start();
unsetSession("username");
unsetSession("id");
header("Location: ".dirname($_SERVER['PHP_SELF'])."/../Pages/logIn.php");
?>