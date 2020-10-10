//Global variables
var boardSize = 19;
var squareSize = 30;
var markedSquare = [-1, -1];
var board = [];
var lastColor = 0;
var playerColor = 0;
var matchIndex = 0;
var nextMoveIndex = 0;
var lastAction = "playStone";
var yourTurnText = "";
var notYourTurnText = "";
var selectLocationText = "";
var areYouSureYouWantToGiveUpText = "";
var blockingPopup = false;
var popupFunction = null;

//Empty board
for(var x = 0; x < 19; x++){
    board[x] = [];    
    for(var y = 0; y < 19; y++){ 
        board[x][y] = 2;
    }    
}

//Place stone
function btnExecute(){
	if(blockingPopup){
		return;
	}
	if(markedSquare[0] == -1){
		showMessage(selectLocationText);
		return;
	}
	try {
		$.ajax({
			type: "POST",
			url: "../Functions/placeStone.php",
			data: {x: markedSquare[0], y: markedSquare[1], matchID: matchIndex},
			success: function(obj, textstatus){
				var obj = JSON.parse(obj);
				if("info" in obj){
					showMessage(obj["info"]);
					if("action" in obj && obj["action"] == "Stone added"){
						board[obj["x"]][obj["y"]] = playerColor;
						captureStones(obj["x"], obj["y"], playerColor);
						markedSquare = [-1, -1];
						nextMoveIndex = nextMoveIndex + 1;
						document.getElementById("yourTurn").innerHTML = notYourTurnText;
						startServerPushing();
						draw();
					}
				}
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}

//Give up
function btnGiveUp(){
	if(blockingPopup){
		return;
	}
	showMessage(areYouSureYouWantToGiveUpText, "YesNo", function(){
		try {
			$.ajax({
				type: "POST",
				url: "../Functions/giveUp.php",
				data: {matchID: matchIndex},
				success: function(obj, textstatus){
					var obj = JSON.parse(obj);
					if("info" in obj){
						if("action" in obj && obj["action"] == "game ended"){
							showMessage(obj["info"], "Ok", function(){window.location.href = "../Pages/main.php";});
						}
						else{
							showMessage(obj["info"]);
						}
					}
				}
			});
		}
		catch(err) {
			window.alert(err.message);
		}
	});
}

//Pass turn
function btnPass(){
	if(blockingPopup){
		return;
	}
	try {
		$.ajax({
			type: "POST",
			url: "../Functions/passTurn.php",
			data: {matchID: matchIndex},
			success: function(obj, textstatus){
				var obj = JSON.parse(obj);
				if("info" in obj){
					if("action" in obj && obj["action"] == "turn passed"){
						markedSquare = [-1, -1];
						nextMoveIndex = nextMoveIndex + 1;
						document.getElementById("yourTurn").innerHTML = notYourTurnText;
						lastAction = "pass";
						startServerPushing();
						draw();
					}
					if("action" in obj && obj["action"] == "game ended"){
						showMessage(obj["info"], "Ok", function(){
							window.location.href = "../Pages/main.php";
						});
					}
					else{
						showMessage(obj["info"]);
					}
				}
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}

//Handle clicks on the canvas
function canvasClick(evt)
{
	if(blockingPopup){
		return;
	}
	var canvas = document.getElementById("goCanvas");
	var rect = canvas.getBoundingClientRect();
	var mousePos = [evt.clientX - rect.left, evt.clientY - rect.top];
	var boardPos = mouseToBoard(mousePos[0], mousePos[1]);
	if(markedSquare[0] == -1){
		if(boardPos[0] > -1 && board[boardPos[0]][boardPos[1]] == 2){
			markedSquare = boardPos;
		}
	}
	else{
		if(boardPos[0] > -1 && board[boardPos[0]][boardPos[1]] == 2 && (boardPos[0] != markedSquare[0] || boardPos[1] != markedSquare[1])){
			markedSquare = boardPos;
		}
		else{
			markedSquare = [-1, -1];
		}
	}
	draw();
}

//Draw the board
function draw(){
	var c = document.getElementById("goCanvas");
	var ctx = c.getContext("2d");

	//Draw board
	ctx.fillStyle = "#DFC156";
	ctx.beginPath();
	ctx.fillRect(0,0,boardSize*squareSize,boardSize*squareSize);

	for (var i=0; i<boardSize; i++){
		ctx.beginPath();
		ctx.moveTo(0.5*squareSize, (i+0.5)*squareSize);
		ctx.lineTo((boardSize-0.5)*squareSize, (i+0.5)*squareSize);
		ctx.stroke();
		ctx.beginPath();
		ctx.moveTo((i+0.5)*squareSize, 0.5*squareSize);
		ctx.lineTo((i+0.5)*squareSize, (boardSize-0.5)*squareSize);
		ctx.stroke();
	}

	//Draw stones
	for (y=0; y<boardSize; y++){
		for (x=0; x<boardSize; x++){
			if(board[x][y] == 0){
					ctx.fillStyle = "#000000";
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.fill();
			}
			if(board[x][y] == 1){
					ctx.fillStyle = "#FFFFFF";
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.fill();
					ctx.fillStyle = "#000000";
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.stroke();
			}
		}
	}
	if(markedSquare[0] > -1){
		ctx.fillStyle = "#FF0000";
		ctx.beginPath();
		ctx.arc((markedSquare[0]+0.5)*squareSize, (markedSquare[1]+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
		ctx.fill();
	}
}

//Loads board from database
function loadBoard(){
	var url = "../Functions/getAllMoves.php?matchIndex=" + matchIndex;
	try{
		$.ajax({
			type: "GET",
			url: url,
			success: function(obj, info, textstatus){
				var obj = JSON.parse(obj);
				var moves = obj["moves"];
				//Empty board
				for(var x = 0; x < 19; x++){
						for(var y = 0; y < 19; y++){ 
								board[x][y] = 2;
						}    
				}
				//Build board
				if(moves.length > 0) {
					for(var moveID=0; moveID<moves.length; moveID++){
						if(moves[moveID]["action"] == "playStone") {
							board[moves[moveID]["x"]][moves[moveID]["y"]] = moveID % 2;
							captureStones(moves[moveID]["x"], moves[moveID]["y"], moveID % 2);
						}
					}
				}
				lastColor = obj["lastColor"];
				nextMoveIndex = obj["currMove"];
				lastAction = obj["lastAction"];
				if(lastColor == 1-playerColor){
					document.getElementById("yourTurn").innerHTML = yourTurnText;
				}
				else{
					startServerPushing();
					document.getElementById("yourTurn").innerHTML = notYourTurnText;
				}
				draw();
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}

//Converts mouse position to board position
function mouseToBoard(x, y){
	var retX = Math.floor(x/squareSize);
	var retY = Math.floor(y/squareSize);
	if(retX<0 || retX>=boardSize || retY<0 || retY>=boardSize){
		retX = -1;
		retY = -1;
	}
	return [retX, retY];
}

//Start server pushing
function startServerPushing(){
	document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> Server pushing started, matchIndex = " + matchIndex + ", moveIndex = " + nextMoveIndex;
	var source = new EventSource("../Functions/getMove.php?matchIndex=" + matchIndex + "&moveIndex=" + nextMoveIndex);
	source.onmessage = function(event) {
		var data = JSON.parse(event.data);
		document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SEE Message: " + JSON.stringify(data);
		source.close();
		//Check if we got match results or next move
		if("winner" in data){
			showMessage(data["info"], "Ok", function(){window.location.href = "../Pages/main.php";});
		}
		else{
			if(data["moveIndex"] == nextMoveIndex){
				//If oponent played a stone, add it to the board
				if(data["action"] == "playStone"){
					board[data["x"]][data["y"]] = 1-playerColor;
					captureStones(data["x"], data["y"], 1-playerColor);
					markedSquare = [-1, -1];
				}
				//If oponent passed and you passed last turn or if oponent surrendered, get game results
				if((data["action"] == "pass" && lastAction == "pass") || data["action"] == "giveUp"){
					var source2 = new EventSource("../Functions/getMatchResult.php?matchIndex=" + matchIndex);
					source2.onmessage = function(event2) {
						var data2 = JSON.parse(event2.data);
						document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> Match result: " + JSON.stringify(data2);
						source2.close();
						showMessage(data2["info"], "Ok", function(){window.location.href = "../Pages/main.php";});
					};
				}
				nextMoveIndex = nextMoveIndex + 1;
				document.getElementById("yourTurn").innerHTML = yourTurnText;
				draw();
			}
		}
	};
	source.onerror = function(event) {
		console.error("SSE error", event);
		document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SSE Error: " + JSON.stringify(event);
		switch (event.target.readyState) {
			case EventSource.CONNECTING:
				console.log('Reconnecting...');
				break;
			case EventSource.CLOSED:
				console.log('Connection failed, will not reconnect');
				break;
		}
	};
	source.onopen = function(event) {
		document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SSE Open: " + event;
	};
}

//Get surrounded stones
function getSurroundedStones(x, y, color){
	var capStones = [];
	var capStonesSize = 0;
	var surrounded = true;
	var checkID = 0;
	if(x>=0 && x<19 && y>=0 && y<19){
		if(board[x][y] == color){
			capStones = [];
			capStones[0] = [x, y];
			capStonesSize = 1;
			surrounded = true;
		}
		else{
			capStones = [];
			capStonesSize = 0;
			surrounded = false;
		}
		checkID = 0;
		while(checkID<capStonesSize && surrounded){
			for(var i=0; i<4 && surrounded; i++){
				if(i==0){
					cX = capStones[checkID][0]-1;
					cY = capStones[checkID][1];
				}
				if(i==1){
					cX = capStones[checkID][0];
					cY = capStones[checkID][1]-1;
				}
				if(i==2){
					cX = capStones[checkID][0];
					cY = capStones[checkID][1]+1;
				}
				if(i==3){
					cX = capStones[checkID][0]+1;
					cY = capStones[checkID][1];
				}
				if(cX>=0 && cX<19 && cY>=0 && cY<19){
					if(board[cX][cY] == color){
						unique = true;
						for(i2=0; i2<capStonesSize && unique; i2++){
							if(capStones[i2] == [cX, cY]){
								unique = false;
							}
						}
						if(unique){
							capStones[capStonesSize] = [cX, cY];
							capStonesSize++;
						}
					}
					if(board[cX][cY] == 2){
						surrounded = false;
						capStones = [];
					}
				}
			}
			checkID++;
		}
	}
	return capStones;
}

//Capture surrounded stones
function captureStones(x, y, color)
{
	var capStones = [];
	var tX;
	var tY;
	for(var i=0; i<4; i++){
		if(i==0){
			tX = x-1;
			tY = y;
		}
		if(i==1){
			tX = x;
			tY = y-1;
		}
		if(i==2){
			tX = x;
			tY = y+1;
		}
		if(i==3){
			tX = x+1;
			tY = y;
		}
		capStones = getSurroundedStones(tX, tY, 1-color);
		if(capStones.length>0){
			for(var i2=0; i2<capStones.length; i2++){
				board[capStones[i2][0]][capStones[i2][1]] = 2;
			}
		}
	}
	return;
}

//Show messages on canvasLabel
function showMessage(msg, timeOrString=3000, func=null)
{
	if(blockingPopup){
		return;
	}
	document.getElementById("canvasMessageDiv").style.display = "inline";
	document.getElementById("canvasLabel").innerHTML = msg;
	if(timeOrString == "Ok"){
		document.getElementById("canvasOkButton").style.display = "inline";
		blockingPopup = true;
		popupFunction = func;
	}	else if(timeOrString == "YesNo"){
		document.getElementById("canvasYesButton").style.display = "inline";
		document.getElementById("canvasNoButton").style.display = "inline";
		blockingPopup = true;
		popupFunction = func;
	}	else {
		setTimeout(
			function(){
				if(blockingPopup){
					return;
				}
				document.getElementById("canvasMessageDiv").style.display = "none";
				document.getElementById("canvasLabel").innerHTML="";
			},timeOrString);
	}
}

//Button yes is clicked
function btnYes()
{
	document.getElementById("canvasMessageDiv").style.display = "none";
	document.getElementById("canvasOkButton").style.display = "none";
	document.getElementById("canvasYesButton").style.display = "none";
	document.getElementById("canvasNoButton").style.display = "none";
	document.getElementById("canvasLabel").innerHTML="";
	blockingPopup = false;
	popupFunction();
	popupFunction = null;
}

//Button no is clicked
function btnNo()
{
	document.getElementById("canvasMessageDiv").style.display = "none";
	document.getElementById("canvasOkButton").style.display = "none";
	document.getElementById("canvasYesButton").style.display = "none";
	document.getElementById("canvasNoButton").style.display = "none";
	document.getElementById("canvasLabel").innerHTML="";
	blockingPopup = false;
	popupFunction = null;
}

//Button ok is clicked
function btnOk()
{
	document.getElementById("canvasMessageDiv").style.display = "none";
	document.getElementById("canvasOkButton").style.display = "none";
	document.getElementById("canvasYesButton").style.display = "none";
	document.getElementById("canvasNoButton").style.display = "none";
	document.getElementById("canvasLabel").innerHTML="";
	blockingPopup = false;
	popupFunction();
	popupFunction = null;
}