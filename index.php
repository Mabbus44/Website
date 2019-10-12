<?php
include("Functions/accountFunctions.php");
if(checkIfLoggedIn()){
	header("Location: Pages/main.php");
}
else{
	header("Location: Pages/logIn.php");
}
?>