//Global variables
var boardSize = 19;
var squareSize = 30;
var markedSquare = [-1, -1];
var board = [];
for(var x = 0; x < 19; x++){
    board[x] = [];    
    for(var y = 0; y < 19; y++){ 
        board[x][y] = 2;
    }    
}

function tempa(){
	window.alert("tempa");
}

//Clear board
function btnClear(){
	try {
		$.ajax({
			type: "POST",
			url: "http://rasmus.today/Functions/clearBoard.php",
			success: function(obj, textstatus){
				var obj = JSON.parse(obj);
				if("error" in obj){
					window.alert(obj);
				}
				markedSquare = [-1, -1];
				loadBoard();
			}
		});
	}
	catch(err) {
		window.alert(err.message);
	}
}

//Place stone
function btnExecute(){
	if(markedSquare[0] == -1){
		return;
	}
	var inputColor = document.getElementById("color").value;	
	try {
		$.ajax({
			type: "POST",
			url: "http://rasmus.today/Functions/placeStone.php",
			data: {x: markedSquare[0], y: markedSquare[1], color: inputColor},
			success: function(obj, textstatus){
				var obj = JSON.parse(obj);
				if("error" in obj){
					window.alert(obj["error"]);
				}
				markedSquare = [-1, -1];
				loadBoard();
				document.getElementById("color").setAttribute("value", 1-inputColor);
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
		if(i>0){
			ctx.moveTo(0, i*squareSize);
			ctx.lineTo(boardSize*squareSize, i*squareSize);
			ctx.stroke();
			ctx.moveTo(i*squareSize, 0);
			ctx.lineTo(i*squareSize, boardSize*squareSize);
			ctx.stroke();
		}
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
function loadBoard(matchIndex){
	window.alert("daj");
	try{
		$.ajax({
			type: "GET",
			url: "http://rasmus.today/Functions/getBoard.php?gameIndex=" + matchIndex,
			success: function(obj, info, textstatus){
				var obj = JSON.parse(obj);
				if("error" in obj){
					window.alert(obj["error"]);
				}
				board = obj["board"];
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