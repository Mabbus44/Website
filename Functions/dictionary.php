<?php
	//Dictionary with all text presented on the website
	function dictRet($keyStr, $arr = NULL){
		session_start();
		if(issetSession("language")){
			$lang = getSession("language");
		}
		else{
			$lang = 0;
		}
		$words = array (
			"English" 								=> array("English",									"中文"),
			"Go" 											=> array("Go",											"-"),
			"Main" 										=> array("Main",										"-"),
			"Challenge" 							=> array("Challenge",								"-"),
			"Challenge players" 			=> array("Challenge players",				"-"),
			"Accept challenge" 				=> array("Accept challenge",				"-"),
			"Log out" 								=> array("Log out",									"-"),
			"Go to game" 							=> array("Go to game",							"-"),
			"Username" 								=> array("Username",								"-"),
			"Password" 								=> array("Password",								"-"),
			"Log in" 									=> array("Log in",									"-"),
			"Create account" 					=> array("Create account",					"-"),
			"Filter" 									=> array("Filter",									"-"),
			"Remove challenge" 				=> array("Remove challenge",				"-"),
			"Profile" 								=> array("Profile",									"-"),
			"Replay" 									=> array("Replay",									"-"),
			"Give up" 								=> array("Give up",									"-"),
			"Pass" 										=> array("Pass",										"-"),
			"Confirm" 								=> array("Confirm",									"-"),
			"Your turn" 							=> array("Your turn",								"-"),
			"Not your turn" 					=> array("Not your turn",						"-"),
			"Enter Username" 					=> array("Enter Username",					"-"),
			"Enter Password" 					=> array("Enter Password",					"-"),
			"Chose color" 						=> array("Chose color",							"-"),
			"Black" 									=> array("Black",										"-"),
			"White" 									=> array("White",										"-"),
			"Board" 									=> array("Board",										"-"),
			"Repeat Password" 				=> array("Repeat Password",					"-"),
			"Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)" 											=> array("Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)",											"-"),
			"Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)" 											=> array("Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)",											"-"),
			"Passwords dont match" 		=> array("Passwords dont match",		"-"),
			"Username already exist"  => array("Username already exist",	"-"),
			"Username does not exist" => array("Username does not exist",	"-"),
			"Wrong password" 					=> array("Wrong password",					"-"),
			"Score winner" 						=> array($arr[0] . "-" . $arr[1] . " score: " . $arr[2] . "-" . $arr[3] . ". Winner: " . $arr[4],			$arr[0] . "-" . $arr[1] . " -: " . $arr[2] . "-" . $arr[3] . ". -: " . $arr[4]),
			"Surrender winner" 				=> array($arr[0] . " surrendered. Winner: " . $arr[1],		$arr[0] . " -. -: " . $arr[1]),
			"It´s not your turn" 			=> array("It´s not your turn",			"-"),
			"Invalid placement" 			=> array("Invalid placement",				"-"),
			"Stone added" 						=> array("Stone added",							"-"),
			"Turn passed" 						=> array("Turn passed",							"-"),
			"Yes" 										=> array("Yes",											"-"),
			"No" 											=> array("No",											"-"),
			"Ok" 											=> array("Ok",											"-"),
			"Are you sure you want to give up?" 											=> array("Are you sure you want to give up?",											"-？"),
			"Select location" 				=> array("Select location",					"-")
		);
		if(array_key_exists($keyStr, $words)){
			return $words[$keyStr][$lang];
		}
		else{
			return "";
		}
	}

	function dict($keyStr, $arr = NULL){
		echo dictRet($keyStr, $arr);
	}
?>