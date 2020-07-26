//Go to game
function btnGoToGame(){
	window.location.href = "../Pages/board.php?id=" + document.getElementById("matchID").value;
}

//Keep selects synced
function setSelect(val){
	document.getElementById("oponentName").selectedIndex = val;
	document.getElementById("matchID").selectedIndex = val;
}
function setSelect2(val){
	document.getElementById("challangerName").selectedIndex = val;
	document.getElementById("challangerID").selectedIndex = val;
}
