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
			"Go" 											=> array("Go",											"围棋"),
			"Main" 										=> array("Main",										"主菜单"),
			"Challenge" 							=> array("Challenge",								"挑战"),
			"Challenge players" 			=> array("Challenge players",				"发起挑战"),
			"Accept challenge" 				=> array("Accept challenge",				"接受挑战"),
			"Log out" 								=> array("Log out",									"退出"),
			"Go to game" 							=> array("Go to game",							"去棋盘页"),
			"Username" 								=> array("Username",								"用户名"),
			"Password" 								=> array("Password",								"密码"),
			"Log in" 									=> array("Log in",									"登录"),
			"Create account" 					=> array("Create account",					"建立账户"),
			"Filter" 									=> array("Filter",									"选择"),
			"Remove challenge" 				=> array("Remove challenge",				"删除挑战"),
			"Profile" 								=> array("Profile",									"主页"),
			"Replay" 									=> array("Replay",									"重玩"),
			"Give up" 								=> array("Give up",									"放弃"),
			"Pass" 										=> array("Pass",										"过"),
			"Confirm" 								=> array("Confirm",									"确定"),
			"Your turn" 							=> array("Your turn",								"轮到你了"),
			"Not your turn" 					=> array("Not your turn",						"这轮不是你走"),
			"Enter Username" 					=> array("Enter Username",					"写入用户名"),
			"Enter Password" 					=> array("Enter Password",					"写入密码"),
			"Chose color" 						=> array("Chose color",							"选颜色"),
			"Black" 									=> array("Black",										"黑色"),
			"White" 									=> array("White",										"白色"),
			"Board" 									=> array("Board",										"棋盘"),
			"Repeat Password" 				=> array("Repeat Password",					"重复密码"),
			"Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)" 											=> array("Invalid username (at least 2 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)",											"用户名不正确 (至少两个字母. 可选字母: 英文字母, 数字 0-9, 特殊字符 - 和 _)"),
			"Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)" 											=> array("Invalid password (at least 3 characters. Allowed characters: any alphabetic characters, numbers 0-9, speial characters - and _)",											"密码不正确 (至少三个字母. 可选字母: 英文字母, 数字 0-9, 特殊字符 - 和 _)"),
			"Passwords dont match" 		=> array("Passwords dont match",		"与刚输入的密码不一致"),
			"Username already exist"  => array("Username already exist",	"用户名已被占用"),
			"Username does not exist" => array("Username does not exist",	"用户名不存在"),
			"Wrong password" 					=> array("Wrong password",					"密码错误"),
			"Score winner" 						=> is_array($arr) && count($arr) >= 5 ? array($arr[0] . "-" . $arr[1] . " score: " . $arr[2] . "-" . $arr[3] . ". Winner: " . $arr[4],			$arr[0] . "-" . $arr[1] . " 得分: " . $arr[2] . "-" . $arr[3] . ". 胜者: " . $arr[4]) : array("",""),
			"Surrender winner" 				=> is_array($arr) && count($arr) >= 2 ? array($arr[0] . " surrendered. Winner: " . $arr[1],		$arr[0] . " 投降. 胜者: " . $arr[1]) : array("",""),
			"It´s not your turn" 			=> array("It´s not your turn",			"这轮不是你走"),
			"Invalid placement" 			=> array("Invalid placement",				"无效放置"),
			"Stone added" 						=> array("Stone added",							"棋子已下定"),
			"Turn passed" 						=> array("Turn passed",							"这轮不下子 轮过"),
			"Yes" 										=> array("Yes",											"是"),
			"No" 											=> array("No",											"不"),
			"Ok" 											=> array("Ok",											"好"),
			"Are you sure you want to give up?" 											=> array("Are you sure you want to give up?",											"确定放弃？"),
			"Select location" 				=> array("Select location",					"选择地点"),
			"Preview score" 					=> array("Preview score",						"分数预览"),
			"Win" 										=> array("Win",											"赢"),
			"Loss" 										=> array("Loss",										"输"),
			"Oponent" 								=> array("Oponent",									"对手"),
			"Score" 									=> array("Score",										"得分"),
			"Surrender" 							=> array("Surrender",								"放弃"),
			"Match history" 					=> array("Match history",						"游戏历史记录"),
			"Rules" 									=> array("Rules",										"规则"),
			"Chess"										=> array("Chess",										"国际象棋"),
			"Check"										=> array("Check",										"Check"),
			"Check mate"							=> array("Check mate",							"Check mate"),
			"Draw"										=> array("Draw",										"Draw"),
			"Someone won"							=> array("[name] won. Start new game as:",										"[name] won. Start new game as:"),
			"Drawresult"							=> array("Draw. Start new game as:","Draw. Start new game as:"),
			"White"										=> array("White",										"White"),
			"Black"										=> array("Black",										"Black"),
			"Start new game"					=> array("Start new game as:",			"Start new game:"),
			"Starting new game"				=> array("Starting new game...",				"Starting new game...")
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