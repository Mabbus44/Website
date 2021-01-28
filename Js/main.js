var language = 0;

//Go to game
function btnGoToGame(){
	if(document.getElementById("matchID").value > 0){
		window.location.href = "../Pages/board.php?id=" + document.getElementById("matchID").value;
	}
}

//Open rules
function btnOpenRules(){
	if(language == 1){
		window.open("../Rules/Rules_ch.pdf");
	}else{
		window.open("../Rules/Rules_en.pdf");
	}
}

//Keep selects synced
function setSelect(val){
	document.getElementById("oponentName").selectedIndex = val;
	document.getElementById("matchID").selectedIndex = val;
}
function setSelect2(val){
	document.getElementById("challengerName").selectedIndex = val;
	document.getElementById("challengerID").selectedIndex = val;
}
