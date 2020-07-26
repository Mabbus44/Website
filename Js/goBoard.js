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

//Empty board
for(var x = 0; x < 19; x++){
    board[x] = [];    
    for(var y = 0; y < 19; y++){ 
        board[x][y] = 2;
    }    
}

//Place stone
function btnExecute(){
	if(markedSquare[0] == -1){
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
					window.alert(obj["info"]);
					if(obj["info"] == "Stone added"){
						board[obj["x"]][obj["y"]] = playerColor;
						captureStones(obj["x"], obj["y"], playerColor);
						markedSquare = [-1, -1];
						nextMoveIndex = nextMoveIndex + 1;
						document.getElementById("yourTurn").innerHTML = "not Your turn!";
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
	if (!confirm('Are you sure you want to give up?')) {
		return 0;
	}
	try {
		$.ajax({
			type: "POST",
			url: "../Functions/giveUp.php",
			data: {matchID: matchIndex},
			success: function(obj, textstatus){
				var obj = JSON.parse(obj);
				if("info" in obj){
					window.alert(obj["info"]);
					if(obj["info"].includes("Game ended")){
						window.location.href = "../Pages/main.php";
					}
				}
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}

//Pass turn
function btnPass(){
	try {
		$.ajax({
			type: "POST",
			url: "../Functions/passTurn.php",
			data: {matchID: matchIndex},
			success: function(obj, textstatus){
				var obj = JSON.parse(obj);
				if("info" in obj){
					window.alert(obj["info"]);
					if(obj["info"] == "Turn passed"){
						markedSquare = [-1, -1];
						nextMoveIndex = nextMoveIndex + 1;
						document.getElementById("yourTurn").innerHTML = "not Your turn!";
						lastAction = "pass";
						startServerPushing();
						draw();
					}
					if(obj["info"].includes("Game ended")){
						window.location.href = "../Pages/main.php";
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
	c.width = boardSize*squareSize;
	c.height = boardSize*squareSize;

	//Draw board
	ctx.fillStyle = "#DFC156";
	ctx.fillRect(0,0,boardSize*squareSize,boardSize*squareSize);
	for (var i=0; i<boardSize; i++){
		ctx.moveTo(0.5*squareSize, (i+0.5)*squareSize);
		ctx.lineTo((boardSize-0.5)*squareSize, (i+0.5)*squareSize);
		ctx.stroke();
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
	var url = "../Functions/getBoard.php?matchIndex=" + matchIndex;
	try{
		$.ajax({
			type: "GET",
			url: url,
			success: function(obj, info, textstatus){
				var obj = JSON.parse(obj);
				board = obj["board"];
				lastColor = obj["lastColor"];
				nextMoveIndex = obj["currMove"];
				lastAction = obj["lastAction"];
				if(lastColor == 1-playerColor){
					document.getElementById("yourTurn").innerHTML = "Your turn!";
				}
				else{
					startServerPushing();
					document.getElementById("yourTurn").innerHTML = "not Your turn!";
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
	document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> Server pushing started";
	var source = new EventSource("../Functions/getMove.php?matchIndex=" + matchIndex + "&moveIndex=" + nextMoveIndex);
	source.onmessage = function(event) {
		var data = JSON.parse(event.data);
		document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SEE Message: " + JSON.stringify(data);
		source.close();
		//Check if we got match results or next move
		if("winner" in data){
			if(data["endCause"] == "pass"){
				window.alert("Game ended. " + $data["winner"] + " won. Score: " + $data["points1"] + "-" + $data["points2"]);
			}
			if(data["endCause"] == "surrender"){
				window.alert($data["winner"] + " won since oponent gave up");
			}
			window.location.href = "../Pages/main.php";
		}
		else{
			if(data["moveIndex"] == nextMoveIndex){
				//If oponent played a stone, add it to the board
				if(data["action"] == "playStone"){
					board[data["x"]][data["y"]] = 1-playerColor;
					captureStones(data["x"], data["y"], 1-playerColor);
					markedSquare = [-1, -1];
				}
				//If oponent passed and you passed last turn, get game results
				if(data["action"] == "pass" && lastAction == "pass"){
					var source2 = new EventSource("../Functions/getMatchResult.php?matchIndex=" + matchIndex);
					source2.onmessage = function(event2) {
						var data2 = JSON.parse(event2.data);
						document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> Match result: " + JSON.stringify(data2);
						source2.close();
						window.alert("Game ended. " + data2["winner"] + " won. Score: " + data2["points1"] + "-" + data2["points2"]);
						window.location.href = "../Pages/main.php";
					};
				}
				//If oponent surrendered, game ended
				if(data["action"] == "giveUp"){
					window.alert("You won since oponent gave up");
					window.location.href = "../Pages/main.php";
				}
				nextMoveIndex = nextMoveIndex + 1;
				document.getElementById("yourTurn").innerHTML = "Your turn!";
				draw();
			}
		}
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