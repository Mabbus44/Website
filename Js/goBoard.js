//Global variables
var boardSize = 19;
var squareSize = 30;
var markedSquare = [-1, -1];
var lastPlacedStone = [-1, -1];
var board = [];
var scoreBoard = [];
var lastColor = 0;
var playerColor = 2;
var matchIndex = 0;
var nextMoveIndex = 0;
var lastAction = "playStone";
var yourTurnText = "";
var notYourTurnText = "";
var selectLocationText = "";
var areYouSureYouWantToGiveUpText = "";
var blockingPopup = false;
var popupFunction = null;
var tabInFocus = true;
const stone = Object.freeze({black:0, white:1, empty:2});

//Flash title class
var tabNotification = {
    Vars:{
				originalTitle: document.title,
        interval: null
    },    
    On: function(notification, intervalSpeed){
        var _this = this;
        _this.Vars.interval = setInterval(function(){document.title = (_this.Vars.originalTitle == document.title) ? notification : _this.Vars.originalTitle;}, (intervalSpeed) ? intervalSpeed : 1000);
    },
    Off: function(){
        clearInterval(this.Vars.interval);
        document.title = this.Vars.originalTitle;   
    }
}

//Remove message when tab gains focus and keep track of if the tab is in focus
$(window).focus(function() {	tabNotification.Off();
															tabInFocus = true;});

$(window).blur(function() {		tabInFocus = false;});

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
						lastPlacedStone = [obj["x"], obj["y"]];
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

//Preview score
function btnPreviewScore(){
	var safeSquares = [];
	var safeSquaresSize = 0;
	var cX = 0;
	var cY = 0;
	var color = -1;
	var isSafe = false;
	var unique = true;
	var checkID = 0;
	var blackScore = 0;
	var whiteScore = 0;
	//Copy board to scoreBoard
	scoreBoard = [];
	for(var x = 0; x < 19; x++){
		scoreBoard[x] = [];
		for(var y = 0; y < 19; y++){
			scoreBoard[x][y] = {safe: -1, stone: board[x][y]};
		}
	}
	//Set safe squares (squares conected to two eyes)
	for(var x = 0; x < 19; x++){
		for(var y = 0; y < 19; y++){
			if(board[x][y]!=stone.empty && scoreBoard[x][y].safe == -1){
				var ret = getSafeStones(x, y, scoreBoard[x][y].stone);
				var safeVal = (ret[2] ? scoreBoard[x][y].stone : stone.empty);
				for(var i=0; i< ret[1]; i++){
					scoreBoard[ret[0][i][0]][ret[0][i][1]].safe = safeVal;
				}
			}
		}
	}
	//Reset all stones that where not safe to -1 so that they will be checked again in next for loop
	//(The reson they where set to 2 where to avoid them being checked multiple times in previous for loop)
	for(var x = 0; x < 19; x++){
		for(var y = 0; y < 19; y++){
			if(scoreBoard[x][y].safe == 2){
				scoreBoard[x][y].safe = -1;
			}
		}
	}
	//Set squares surrounded by safe squares to also be safe
	for(var x = 0; x < 19; x++){
		for(var y = 0; y < 19; y++){
			if(scoreBoard[x][y].safe == -1){
				safeSquares = [];
				safeSquares[0] = [x, y];
				safeSquaresSize = 1;
				checkID = 0;
				color = -1;
				isSafe = true;
				//Check neighbours of all safe squares found
				while(checkID<safeSquaresSize){
					for(var i=0; i<4; i++){
						if(i==0){
							cX = safeSquares[checkID][0]-1;
							cY = safeSquares[checkID][1];
						}
						if(i==1){
							cX = safeSquares[checkID][0];
							cY = safeSquares[checkID][1]-1;
						}
						if(i==2){
							cX = safeSquares[checkID][0];
							cY = safeSquares[checkID][1]+1;
						}
						if(i==3){
							cX = safeSquares[checkID][0]+1;
							cY = safeSquares[checkID][1];
						}
						if(cX>=0 && cX<19 && cY>=0 && cY<19){
							//If unchecked square, expand safeSquares
							if(scoreBoard[cX][cY].safe == -1){
								unique = true;
								for(var i2=0; i2<safeSquaresSize && unique; i2++){
									if(arraysEqual(safeSquares[i2], [cX, cY])){
										unique = false;
									}
								}
								if(unique){
									safeSquares[safeSquaresSize] = [cX, cY];
									safeSquaresSize++;
								}
							}
							else{
								//If checked square, if first color, save it. If different color, square is not safe
								if(color == -1 && scoreBoard[cX][cY].safe != stone.empty){
									color = scoreBoard[cX][cY].safe;
								}
								else{
									if(scoreBoard[cX][cY].safe != color){
										isSafe = false;
									}
								}
							}
						}
					}
					checkID++;
				}
				//Set safe value for squares just checked
				if(color == -1 || isSafe == false){
					color = stone.empty;
				}
				for(var i=0; i<safeSquaresSize; i++){
					scoreBoard[safeSquares[i][0]][safeSquares[i][1]].safe = color;
				}
			}
		}
	}
	//Set score
	for(var x = 0; x < 19; x++){
		for(var y = 0; y < 19; y++){
			if(scoreBoard[x][y].safe == stone.black){
				blackScore++;
			}else if(scoreBoard[x][y].safe == stone.white){
				whiteScore++;
			}else{
				if(scoreBoard[x][y].stone == stone.black){
					blackScore++;
				}else if(scoreBoard[x][y].stone == stone.white){
					whiteScore++;
				}
			}				
		}
	}
	drawScore(blackScore, whiteScore);
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
	ctx.strokeStyle = "#000000";
	ctx.lineWidth = 1;

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
					ctx.strokeStyle = "#000000";
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.stroke();
			}
		}
	}

	//Mark last placed stone
	if(lastPlacedStone[0] > -1){
		ctx.strokeStyle = "#FFFF00";
		ctx.lineWidth = 2;
		ctx.beginPath();
		ctx.arc((lastPlacedStone[0]+0.5)*squareSize, (lastPlacedStone[1]+0.5)*squareSize, squareSize*0.4+1, 0, 2 * Math.PI);
		ctx.stroke();
	}
	
	//Mark selected stone
	if(markedSquare[0] > -1){
		ctx.fillStyle = "#FF0000";
		ctx.beginPath();
		ctx.arc((markedSquare[0]+0.5)*squareSize, (markedSquare[1]+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
		ctx.fill();
	}

	//Show arrow on player whos turn it is
	var whiteArrow = document.getElementById("whiteArrow");
	var blackArrow = document.getElementById("blackArrow");
	if(lastColor == 0){
		whiteArrow.style.display = "block";
		blackArrow.style.display = "none";
	}else{
		whiteArrow.style.display = "none";
		blackArrow.style.display = "block";
	}

	//Erase scores
	document.getElementById("blackScore").innerHTML = "";
	document.getElementById("whiteScore").innerHTML = "";
}

//Draw the scoreBoard
function drawScore(blackScore, whiteScore){
	var c = document.getElementById("goCanvas");
	var ctx = c.getContext("2d");

	//Draw board
	for (y=0; y<boardSize; y++){
		for (x=0; x<boardSize; x++){
			if(scoreBoard[x][y].safe == stone.empty){
				ctx.fillStyle = "#DFC156";
			}else if(scoreBoard[x][y].safe == stone.white){
				ctx.fillStyle = "#BBBBBB";
			}else if(scoreBoard[x][y].safe == stone.black){
				ctx.fillStyle = "#444444";
			}
			ctx.beginPath();
			ctx.fillRect(x*squareSize,y*squareSize,x*squareSize+squareSize,y*squareSize+squareSize);
		}
	}

	ctx.strokeStyle = "#000000";
	ctx.lineWidth = 1;

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
			if(scoreBoard[x][y].stone == stone.black){
					ctx.fillStyle = "#000000";
					ctx.lineWidth = 1;
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.fill();
			}
			if(scoreBoard[x][y].stone == stone.white){
					ctx.fillStyle = "#FFFFFF";
					ctx.lineWidth = 1;
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.fill();
					ctx.strokeStyle = "#000000";
					ctx.beginPath();
					ctx.arc((x+0.5)*squareSize, (y+0.5)*squareSize, squareSize*0.4, 0, 2 * Math.PI);
					ctx.stroke();
			}
			if((scoreBoard[x][y].stone == stone.black && scoreBoard[x][y].safe == stone.white) || (scoreBoard[x][y].stone == stone.white && scoreBoard[x][y].safe == stone.black)){
				ctx.strokeStyle = "#FF0000";
				ctx.lineWidth = 2;
				ctx.beginPath();
				ctx.moveTo((x+0.1)*squareSize, (y+0.1)*squareSize);
				ctx.lineTo((x+0.9)*squareSize, (y+0.9)*squareSize);
				ctx.moveTo((x+0.9)*squareSize, (y+0.1)*squareSize);
				ctx.lineTo((x+0.1)*squareSize, (y+0.9)*squareSize);
				ctx.stroke();
			}
		}
	}

	//Hide arrows
	document.getElementById("whiteArrow").style.display = "none";
	document.getElementById("blackArrow").style.display = "none";

	//Write scores
	document.getElementById("blackScore").innerHTML = "- " + blackScore;
	document.getElementById("whiteScore").innerHTML = "- " + whiteScore;
}

//Loads board from database
function loadBoard(){
	var url = "../Functions/getAllMoves.php?matchIndex=" + matchIndex;
	lastPlacedStone = [-1, -1];
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
							if(moveID == moves.length-1){
								lastPlacedStone = [moves[moveID]["x"], moves[moveID]["y"]];
							}
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
	//document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> Server pushing started, matchIndex = " + matchIndex + ", moveIndex = " + nextMoveIndex;
	var source = new EventSource("../Functions/getMove.php?matchIndex=" + matchIndex + "&moveIndex=" + nextMoveIndex);
	source.onmessage = function(event) {
		var data = JSON.parse(event.data);
		//document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SEE Message: " + JSON.stringify(data);
		source.close();
		//Check if we got match results or next move
		if("winner" in data){
			showMessage(data["info"], "Ok", function(){window.location.href = "../Pages/main.php";});
		}
		else{
			if(data["moveIndex"] == nextMoveIndex){
				//If oponent played a stone, add it to the board
				if(data["action"] == "playStone"){
					var placedColor = data["moveIndex"] % 2;
					board[data["x"]][data["y"]] = placedColor;
					captureStones(data["x"], data["y"], placedColor);
					markedSquare = [-1, -1];
					lastPlacedStone = [data["x"], data["y"]];
				}
				//If oponent passed and you passed last turn or if oponent surrendered, get game results
				if((data["action"] == "pass" && lastAction == "pass") || data["action"] == "giveUp"){
					var source2 = new EventSource("../Functions/getMatchResult.php?matchIndex=" + matchIndex);
					source2.onmessage = function(event2) {
						var data2 = JSON.parse(event2.data);
						//document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> Match result: " + JSON.stringify(data2);
						source2.close();
						showMessage(data2["info"], "Ok", function(){window.location.href = "../Pages/main.php";});
					};
				}
				nextMoveIndex = nextMoveIndex + 1;
				document.getElementById("yourTurn").innerHTML = yourTurnText;
				if(!tabInFocus){
					tabNotification.On("Your turn!");
				}
				draw();
			}
		}
	};
	source.onerror = function(event) {
		console.error("SSE error", event);
		//document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SSE Error: " + JSON.stringify(event);
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
		//document.getElementById("dbg").innerHTML = document.getElementById("dbg").innerHTML + "<br> SSE Open: " + event;
	};
}

//Get safe stones
function getSafeStones(x, y, color){
	var safeStones = [];
	var safeStonesSize = 0;
	var eyeSquares = [];
	var eyeSquaresSize = 0;
	var tempEyeSquares = [];
	var tempEyeSquaresSize = 0;
	var checkID = 0;
	var checkID2 = 0;
	var eyeCount = 0;
	var isSafe = false;
	var unique = true;
	var isEye = false;
	var cX2 = 0;
	var cY2 = 0;
	//Check so coordinates is inside board and right color stone is in that spot
	if(x>=0 && x<19 && y>=0 && y<19){
		if(scoreBoard[x][y].stone == color){
			safeStones = [];
			safeStones[0] = [x, y];
			safeStonesSize = 1;
		}
		else{
			safeStones = [];
			safeStonesSize = 0;
		}
		checkID = 0;
		//Check neighbours of all safe stones found
		while(checkID<safeStonesSize){
			for(var iX=-1; iX<2; iX++){
				for(var iY=-1; iY<2; iY++){
					var cX=safeStones[checkID][0]+iX;
					var cY=safeStones[checkID][1]+iY;
					//Check that neighbour is inside board, and is not itself
					if(cX>=0 && cX<19 && cY>=0 && cY<19 && !(iX==0 && iY==0)){
						//If it is a diagonal neighbour, check that both stones bewteen is not oponent stones
						if(iX != 0 && iY != 0 && scoreBoard[cX-iX][cY].stone == 1-color && scoreBoard[cX][cY-iY].stone == 1-color){
						}
						else{
							//If friendly stone, expand safeStones
							if(scoreBoard[cX][cY].stone == color){
								unique = true;
								for(i=0; i<safeStonesSize && unique; i++){
									if(arraysEqual(safeStones[i], [cX, cY])){
										unique = false;
									}
								}
								if(unique){
									safeStones[safeStonesSize] = [cX, cY];
									safeStonesSize++;
								}
							}
							//If empty or oponent spot and not diagonal, check for eyes (stop checking when 2 eyes are found)
							if((iX == 0 || iY == 0) && scoreBoard[cX][cY].stone != color && eyeCount<2){
								unique = true;
								for(i=0; i<eyeSquaresSize && unique; i++){
									if(arraysEqual(eyeSquares[i], [cX, cY])){
										unique = false;
									}
								}
								//If spot is not already checked for eyes, check it
								isEye = false;
								if(unique){
									tempEyeSquares = [];
									tempEyeSquares[0] = [cX, cY];
									tempEyeSquaresSize = 1;
									isEye = true;
									checkID2 = 0;
									while(checkID2<tempEyeSquaresSize && isEye){
										for(var i=0; i<4 && isEye; i++){
											if(i==0){
												cX2 = tempEyeSquares[checkID2][0]-1;
												cY2 = tempEyeSquares[checkID2][1];
											}
											if(i==1){
												cX2 = tempEyeSquares[checkID2][0];
												cY2 = tempEyeSquares[checkID2][1]-1;
											}
											if(i==2){
												cX2 = tempEyeSquares[checkID2][0];
												cY2 = tempEyeSquares[checkID2][1]+1;
											}
											if(i==3){
												cX2 = tempEyeSquares[checkID2][0]+1;
												cY2 = tempEyeSquares[checkID2][1];
											}
											if(cX2>=0 && cX2<19 && cY2>=0 && cY2<19){
												if(scoreBoard[cX2][cY2].stone != color){
													unique = true;
													for(i2=0; i2<tempEyeSquaresSize && unique; i2++){
														if(arraysEqual(tempEyeSquares[i2], [cX2, cY2])){
															unique = false;
														}
													}
													if(unique){
														tempEyeSquares[tempEyeSquaresSize] = [cX2, cY2];
														tempEyeSquaresSize++;
														//En eye is defined as surrounded area of maximum size 25 (may contain oponent stones)
														if(tempEyeSquaresSize>25){
															isEye = false;
														}
													}
												}
											}
										}
										checkID2++;
									}
								}
								//Add new checked squares to checked squares array
								for(var i=0; i<tempEyeSquaresSize; i++){
									eyeSquares[eyeSquaresSize+i] = tempEyeSquares[i];
								}
								eyeSquaresSize += tempEyeSquaresSize;
								tempEyeSquares = [];
								tempEyeSquaresSize = 0;
								if(isEye){
									eyeCount++;
								}
							}
						}
					}
				}
			}
			checkID++;
		}
	}
	if(eyeCount > 1){
		isSafe = true;
	}
	return [safeStones, safeStonesSize, isSafe];
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
							if(arraysEqual(capStones[i2], [cX, cY])){
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

//Check if arrays are equal
function arraysEqual(a, b) {
  if (a === b) return true;
  if (a == null || b == null) return false;
  if (a.length !== b.length) return false;
  for (var i = 0; i < a.length; ++i) {
    if (a[i] !== b[i]) return false;
  }
  return true;
}