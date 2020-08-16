<?php
	//Dictionary with all text presented on the website
	function dictRet($keyStr){
		session_start();
		if(issetSession("language")){
			$lang = getSession("language");
		}
		else{
			$lang = 0;
		}
		$words = array (
			"English" 								=> array("English",									"中文"),
			"Go" 											=> array("Go",											"围棋"),
			"Main" 										=> array("Main",										"主要"),
			"Challenge" 							=> array("Challenge",								"挑战"),
			"Challenge players" 			=> array("Challenge players",				"挑战玩家"),
			"Accept challenge" 				=> array("Accept challenge",				"接受挑战"),
			"Log out" 								=> array("Log out",									"登出"),
			"Go to game" 							=> array("Go to game",							"去游戏"),
			"Username" 								=> array("Username",								"用户名"),
			"Password" 								=> array("Password",								"密码"),
			"Log in" 									=> array("Log in",									"登录"),
			"Create account" 					=> array("Create account",					"创建帐号"),
			"Filter" 									=> array("Filter",									"过滤"),
			"Remove challenge" 				=> array("Remove challenge",				"消除挑战"),
			"Profile" 								=> array("Profile",									"个人资料"),
			"Replay" 									=> array("Replay",									"重播"),
			"Give up" 								=> array("Give up",									"放弃"),
			"Pass" 										=> array("Pass",										"通过"),
			"Confirm" 								=> array("Confirm",									"确认"),
			"Your turn" 							=> array("Your turn",								"轮到你"),
			"Not your turn" 					=> array("Not your turn",						"轮到你了"),
			"Enter Username" 					=> array("Enter Username",					"输入用户名"),
			"Enter Password" 					=> array("Enter Password",					"输入密码"),
			"Chose color" 						=> array("Chose color",							"选择颜色"),
			"Black" 									=> array("Black",										"黑色"),
			"White" 									=> array("White",										"白色"),
			"Board" 									=> array("Board",										"板"),
			"Repeat Password" 				=> array("Repeat Password",					"重复输入密码"),
			"Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)" 											=> array("Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)",											"用户名无效（至少2个字符。允许的字符：任何字母字符，数字0-9，特殊字符-和_）"),
			"Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)" 											=> array("Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)",											"无效的密码（至少3个字符。允许的字符：任何字母字符，数字0-9，特殊字符-和_）"),
			"Passwords dont match" 		=> array("Passwords dont match",		"密码不匹配"),
			"Username already exist"  => array("Username already exist",	"用户名已存在"),
			"Username does not exist" 											=> array("Username does not exist",											"用户名不存在"),
			"Wrong password" 											=> array("Wrong password",											"密码错误"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
			"Go" 											=> array("Go",											"围棋"),
		);
		if(array_key_exists($keyStr, $words)){
			return $words[$keyStr][$lang];
		}
		else{
			return "";
		}
	}

	function dict($keyStr){
		echo dictRet($keyStr);
	}
?>