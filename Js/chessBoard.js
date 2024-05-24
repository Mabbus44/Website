//Global variables
const squareSize = 60;
const drawThrottler = new Semaphore();
var triedGettingNamesisMatch = false;
var game = null;
var allowMoves = false;
var playerColor = "";
var selectedSquare = null;
var blockingPopup = false;
var markedSquare = null;
var markedPossibleDestinations = null;
var ignoreNextSSEError = false;
var SSE = null;
var newGameSSE = null;

//Pieces and their promises
const pieceNames = [
  "VT",
  "VH",
  "VL",
  "VD",
  "VK",
  "VB",
  "ST",
  "SH",
  "SL",
  "SD",
  "SK",
  "SB",
];
const pieceIcons = new Map();
const loadProms = [];
pieceNames.forEach((name) => {
  pieceIcons[name] = new Image();
  loadProms.push(
    new Promise((res) => {
      pieceIcons[name].onload = () => {
        res(true);
      };
    })
  );
});

var domLoadedResolver = [];
loadProms.push(
  new Promise(function (resolve, reject) {
    domLoadedResolver = { resolve: resolve, reject: reject };
  })
);
var boardLoadedResolver = [];
loadProms.push(
  new Promise(function (resolve, reject) {
    boardLoadedResolver = { resolve: resolve, reject: reject };
  })
);

//Images, board and DOM finished loading, time to draw the board
Promise.all(loadProms).then((data) => {
  //Setting game to null to trigger check to fetch new moves
  newGame = game;
  game = null;
  setGameAndDraw(newGame);
});

pieceNames.forEach((name) => {
  pieceIcons[name].src = "https://rasmus.today/Graphics/Chess/" + name + ".png";
});

//Loads board from database
function loadBoard() {
  //If no valid matchid given, show overlay to start new game
  if (matchId <= 0) {
    getNemesisMatch(true);
    return;
  }

  //Load board
  var url = "../Functions/getChessBoard.php?matchIndex=" + matchId;
  try {
    $.ajax({
      type: "GET",
      url: url,
      success: function (obj, info, textstatus) {
        try {
          var obj = JSON.parse(obj);
        } catch (e) {
          console.log("Parse failed in loadBoard: " + obj);
          matchId == 0;
          getNemesisMatch(true);
          return;
        }
        //If load board fails, show overlay to start new game
        if ("error" in obj) {
          console.log("Error in loadBoard: " + obj["error"]);
          matchId == 0;
          getNemesisMatch(true);
          return;
        }
        game = obj["data"];
        if (game["whiteId"] == playerId) {
          playerColor = "white";
        }
        if (game["blackId"] == playerId) {
          playerColor = "black";
        }
        boardLoadedResolver.resolve(true);
        setPlayerNames(game["whiteName"], game["blackName"]);
      },
    });
  } catch (err) {
    window.alert(err.message);
  }
}

//Checks if we have an ongoing game agains our nemesis
function getNemesisMatch(resolve) {
  //Only try once
  if (triedGettingNamesisMatch) return;
  triedGettingNamesisMatch = true;
  //Look for nemesis match
  console.log("getNemesisMatch()");
  var url = "../Functions/getNemesisMatch.php";
  try {
    $.ajax({
      type: "GET",
      url: url,
      success: function (obj, info, textstatus) {
        try {
          var obj = JSON.parse(obj);
        } catch (e) {
          console.log("Parse failed in getNemesisMatch: ");
          console.log(obj);
          if (resolve) boardLoadedResolver.resolve(true);
          checkForNewGame();
          return;
        }
        //If load board fails, show overlay to start new game
        if ("error" in obj) {
          console.log("Error in getNemesisMatch: " + obj["error"]);
          if (resolve) boardLoadedResolver.resolve(true);
          checkForNewGame();
          return;
        }
        if (!obj["matchOver"]) {
          window.location.href = "../Pages/chessBoard.php?id=" + obj["matchId"];
          return;
        }
        if (resolve) boardLoadedResolver.resolve(true);
        checkForNewGame();
        return;
      },
    });
  } catch (err) {
    window.alert(err.message);
  }
}

//Sets player name next to the board
function setPlayerNames(whiteName, blackName) {
  document.getElementById("whiteName").innerHTML = whiteName;
  document.getElementById("blackName").innerHTML = blackName;
}

//Load board
loadBoard();

//Called when the DOM finnished loading
function domLoaded() {
  domLoadedResolver.resolve(true);
  const canvas = document.getElementById("chessBoardCanvas");
  canvas.width = squareSize * 8;
  canvas.height = squareSize * 8;
  const canvasMsg = document.getElementById("chessCanvasMessageDiv");
  canvasMsg.style.left = squareSize * 8;
  canvasMsg.style.top = squareSize * 8;
  canvasMsg.setAttribute(
    "style",
    "left:" + (squareSize * 8) / 2 + "px; top:" + (squareSize * 8) / 2 + "px"
  );
  const namesColumn = document.getElementById("namesColumn");
  namesColumn.style.height = canvas.height;
  namesColumn.setAttribute("style", "height:" + canvas.height + "px");
  canvas.addEventListener("click", canvasClick, false);
}

//Draw the board
function setGameAndDraw(newGame = null) {
  drawThrottler.callFunction((newGame) => {
    console.log("Draw");
    var c = document.getElementById("chessBoardCanvas");
    var ctx = c.getContext("2d");

    //Set game if newGame is actually newer
    if (
      (game == null && newGame != null) ||
      (newGame != null && newGame["moves"].length > game["moves"].length)
    ) {
      game = newGame;
      markedSquare = null;
      markedPossibleDestinations = [];
      if (playerColor != game["color"]) checkForNewMoves(game["moves"].length);
    }

    //Show whos turn it is
    showWhosTurnItIs();
    if (!allowMoves) markedSquare = null;

    //Check if game is over
    if (
      game == null ||
      game["checkStatus"] == "checkmate" ||
      game["checkStatus"] == "draw"
    ) {
      showMatchOverOverlay();
    }

    //Draw board
    ctx.fillStyle = "#DFC156";
    ctx.fillRect(0, 0, 8 * squareSize, 8 * squareSize);
    white = true;
    for (var y = 0; y < 8; y++) {
      for (var x = 0; x < 8; x++) {
        //Decide color
        if (white) {
          if (
            (markedSquare != null &&
              markedSquare[0] == x &&
              markedSquare[1] == 7 - y) ||
            (game != null &&
              game["moves"].length > 0 &&
              game["moves"][game["moves"].length - 1]["fromX"] == x &&
              game["moves"][game["moves"].length - 1]["fromY"] == 7 - y) ||
            (game != null &&
              game["moves"].length > 0 &&
              game["moves"][game["moves"].length - 1]["toX"] == x &&
              game["moves"][game["moves"].length - 1]["toY"] == 7 - y)
          )
            ctx.fillStyle = "#F5F682";
          else ctx.fillStyle = "#EBECD0";
        } else {
          if (
            (markedSquare != null &&
              markedSquare[0] == x &&
              markedSquare[1] == 7 - y) ||
            (game != null &&
              game["moves"].length > 0 &&
              game["moves"][game["moves"].length - 1]["fromX"] == x &&
              game["moves"][game["moves"].length - 1]["fromY"] == 7 - y) ||
            (game != null &&
              game["moves"].length > 0 &&
              game["moves"][game["moves"].length - 1]["toX"] == x &&
              game["moves"][game["moves"].length - 1]["toY"] == 7 - y)
          )
            ctx.fillStyle = "#B9CA43";
          else ctx.fillStyle = "#739552";
        }
        white = !white;
        //Draw square
        ctx.fillRect(
          swapBoard(x) * squareSize,
          swapBoard(y) * squareSize,
          squareSize,
          squareSize
        );
      }
      white = !white;
    }

    if (
      markedPossibleDestinations != null &&
      markedPossibleDestinations.length > 0
    ) {
      for (possibleDestination in markedPossibleDestinations) {
        x = markedPossibleDestinations[possibleDestination][0];
        y = markedPossibleDestinations[possibleDestination][1];
        white = true;
        if ((x + y) % 2 == 0) white = false;
        if (white) ctx.fillStyle = "#646659";
        else ctx.fillStyle = "#314123";
        ctx.beginPath();
        if (game["board"][y][x] != "") {
          ctx.arc(
            (swapBoard(x) + 0.5) * squareSize,
            (swapBoard(7 - y) + 0.5) * squareSize,
            squareSize / 2.5,
            0,
            2 * Math.PI
          );
        } else {
          ctx.arc(
            (swapBoard(x) + 0.5) * squareSize,
            (swapBoard(7 - y) + 0.5) * squareSize,
            squareSize / 5,
            0,
            2 * Math.PI
          );
        }
        ctx.fill();
      }
    }

    if (game != null) {
      for (var y = 0; y < 8; y++) {
        for (var x = 0; x < 8; x++) {
          if (game["board"][y][x] != "") {
            ctx.drawImage(
              pieceIcons[game["board"][y][x]],
              swapBoard(x) * squareSize,
              swapBoard(7 - y) * squareSize,
              squareSize,
              squareSize
            );
          }
        }
      }
    }

    return new Promise((res) => res(true));
  }, newGame);
}

//Flips the board if the player is black
function swapBoard(c) {
  if (playerColor == "black") return 7 - c;
  return c;
}

//Show correct html elements according to whos turn it is
function showWhosTurnItIs() {
  //If no game, or game is over, show nothing
  if (
    game == null ||
    game["checkStaus"] == "checkmate" ||
    game["checkStatus"] == "draw"
  ) {
    document.getElementById("checkLabel").innerHTML = "";
    document.getElementById("checkDiv").style.display = "none";
    document.getElementById("whiteArrow").style.display = "none";
    document.getElementById("blackArrow").style.display = "none";
    document.getElementById("yourTurn").innerHTML = notYourTurnText;
    document.getElementById("yourTurnDiv").style.display = "none";
    return;
  }
  if (
    (game["color"] == "white" && game["whiteId"] == playerId) ||
    (game["color"] == "black" && game["blackId"] == playerId)
  ) {
    allowMoves = true;
  } else {
    allowMoves = false;
  }
  if (game["checkStatus"] == "check") {
    document.getElementById("checkLabel").innerHTML = checkText;
    document.getElementById("checkDiv").style.display = "block";
  } else if (game["checkStatus"] == "checkmate") {
    document.getElementById("checkLabel").innerHTML = checkMateText;
    document.getElementById("checkDiv").style.display = "block";
  } else if (game["checkStatus"] == "draw") {
    document.getElementById("checkLabel").innerHTML = drawText;
    document.getElementById("checkDiv").style.display = "block";
  } else {
    document.getElementById("checkLabel").innerHTML = "";
    document.getElementById("checkDiv").style.display = "none";
  }
  if (allowMoves) {
    document.getElementById("yourTurn").innerHTML = yourTurnText;
    document.getElementById("yourTurnDiv").style.display = "block";
  } else {
    document.getElementById("yourTurn").innerHTML = notYourTurnText;
    document.getElementById("yourTurnDiv").style.display = "none";
  }
  if (game["color"] == "white") {
    document.getElementById("whiteArrow").style.display = "block";
    document.getElementById("blackArrow").style.display = "none";
  } else {
    document.getElementById("whiteArrow").style.display = "none";
    document.getElementById("blackArrow").style.display = "block";
  }
}

//Handle clicks on the canvas
function canvasClick(evt) {
  //Check what was clicked
  if (blockingPopup || !allowMoves) {
    return;
  }
  var canvas = document.getElementById("chessBoardCanvas");
  var rect = canvas.getBoundingClientRect();
  var mousePos = [evt.clientX - rect.left, evt.clientY - rect.top];
  var boardPos = mouseToBoard(mousePos[0], mousePos[1]);
  if (boardPos == null) {
    markedSquare == null;
    markedPossibleDestinations = null;
    return;
  }

  //Check if own piece was clicked
  var clickedPiece = game["board"][boardPos[1]][boardPos[0]];
  if (
    typeof clickedPiece == "string" &&
    ((clickedPiece.includes("V") && playerColor == "white") ||
      (clickedPiece.includes("S") && playerColor == "black"))
  ) {
    markedSquare = boardPos;
    markedPossibleDestinations = [];
    for (possibleMove in game["possibleMoves"]) {
      if (
        game["possibleMoves"][possibleMove]["fromX"] == boardPos[0] &&
        game["possibleMoves"][possibleMove]["fromY"] == boardPos[1]
      ) {
        markedPossibleDestinations.push([
          game["possibleMoves"][possibleMove]["toX"],
          game["possibleMoves"][possibleMove]["toY"],
        ]);
      }
    }
    setGameAndDraw();
    return;
  }

  //Check if destination was clicked
  if (
    markedPossibleDestinations != null &&
    markedPossibleDestinations.length > 0
  ) {
    for (possibleDestination in markedPossibleDestinations) {
      x = markedPossibleDestinations[possibleDestination][0];
      y = markedPossibleDestinations[possibleDestination][1];
      if (x == boardPos[0] && y == boardPos[1]) {
        makeMove(markedSquare[0], markedSquare[1], x, y);
        markedSquare = null;
        markedPossibleDestinations = null;
        setGameAndDraw();
        return;
      }
    }
  }
  markedSquare = null;
  markedPossibleDestinations = null;
  setGameAndDraw();
  return;
}

//Converts mouse position to board position
function mouseToBoard(x, y) {
  var retX = Math.floor(x / squareSize);
  var retY = Math.floor(y / squareSize);
  if (retX < 0 || retX >= 8 || retY < 0 || retY >= 8) {
    return null;
  }
  retY = 7 - retY;
  return [swapBoard(retX), swapBoard(retY)];
}

//Make move
function makeMove(fromX, fromY, toX, toY) {
  try {
    $.ajax({
      type: "POST",
      url: "../Functions/makeChessMove.php",
      data: {
        fromX: fromX,
        fromY: fromY,
        toX: toX,
        toY: toY,
        matchId: matchId,
      },
      success: function (obj, textstatus) {
        console.log("Move done");
        if (obj != null) {
          try {
            var newGame = JSON.parse(obj);
          } catch (e) {
            console.log("Error in makeMove: " + e);
            console.log(obj);
            return;
          }
          if ("error" in newGame) {
            console.log("Error in makeMove: " + newGame["error"]);
            return;
          }
          setGameAndDraw(newGame);
        }
      },
    });
  } catch (err) {
    window.alert(err.message);
  }
}

//Check for new moves
function checkForNewMoves(moveId) {
  console.log("checkForNewMoves(" + moveId + ")");
  SSE = new EventSource(
    "../Functions/getChessMove.php?matchId=" + matchId + "&moveId=" + moveId
  );
  SSE.addEventListener("restart", (event) => {
    ignoreNextSSEError = true;
  });

  SSE.onmessage = function (event) {
    console.log("Got new move");
    var newGame = JSON.parse(event.data);
    stopCheckingForNewMoves();
    setGameAndDraw(newGame);
  };
  SSE.onerror = function (event) {
    if (
      ignoreNextSSEError &&
      event.target.readyState == EventSource.CONNECTING
    ) {
      ignoreNextSSEError = false;
      return;
    }
    console.error("SSE error", event);
    switch (event.target.readyState) {
      case EventSource.CONNECTING:
        console.log("Reconnecting...");
        break;
      case EventSource.CLOSED:
        console.log("Connection failed, will not reconnect");
        break;
    }
  };
  SSE.onopen = function (event) {};
}

//Stop looking for new moves
function stopCheckingForNewMoves() {
  if (SSE) {
    console.log("Stopping SSE");
    SSE.close();
  }
  SSE = null;
}

//CheckForNewGame
function checkForNewGame() {
  console.log("checkForNewGame()");
  newGameSSE = new EventSource("../Functions/getNemesisMatchSSE.php");
  newGameSSE.addEventListener("restart", (event) => {
    ignoreNextNewGameSSEError = true;
  });

  newGameSSE.onmessage = function (event) {
    console.log("Got new game");
    console.log(event.data);
    var newGame = JSON.parse(event.data);
    stopCheckingForNewGame();
    window.location.href = "../Pages/chessBoard.php?id=" + newGame["matchId"];
  };
  newGameSSE.onerror = function (event) {
    if (
      ignoreNextNewGameSSEError &&
      event.target.readyState == EventSource.CONNECTING
    ) {
      ignoreNextNewGameSSEError = false;
      return;
    }
    console.error("New game SSE error", event);
    switch (event.target.readyState) {
      case EventSource.CONNECTING:
        console.log("Reconnecting...");
        break;
      case EventSource.CLOSED:
        console.log("Connection failed, will not reconnect");
        break;
    }
  };
  newGameSSE.onopen = function (event) {};
}

//Stop looking for new game
function stopCheckingForNewGame() {
  if (newGameSSE) {
    console.log("Stopping new game SSE");
    newGameSSE.close();
  }
  newGameSSE = null;
}

//Shows match over overlay
function showMatchOverOverlay() {
  getNemesisMatch();
  document.getElementById("chessCanvasMessageDiv").style.display = "inline";
  if (game == null) {
    document.getElementById("canvasLabel").innerHTML = newGameText;
  } else if (game["checkStatus"] == "checkmate") {
    var displayText = someoneWonText;
    if (game["color"] == "black")
      displayText = displayText.replace("[name]", game["whiteName"]);
    else displayText = displayText.replace("[name]", game["blackName"]);
    document.getElementById("canvasLabel").innerHTML = displayText;
  } else {
    document.getElementById("canvasLabel").innerHTML = drawText;
  }
  blockingPopup = true;
}

//Start new game
function startNewGame(newGameColor) {
  stopCheckingForNewGame();
  document.getElementById("canvasLabel").innerHTML = startingNewGameText;
  document.getElementById("canvasWhiteButton").style.display = "none";
  document.getElementById("canvasBlackButton").style.display = "none";
  try {
    $.ajax({
      type: "POST",
      url: "../Functions/startChessGame.php",
      data: {
        color: newGameColor,
      },
      success: function (obj, textstatus) {
        console.log("New game created");
        if (obj != null) {
          try {
            var newGame = JSON.parse(obj);
          } catch (e) {
            console.log("Error in startNewGame: " + e);
            console.log(obj);
            return;
          }
          if ("error" in newGame) {
            console.log("Error in startNewGame: " + newGame["error"]);
            return;
          }
          window.location.href = "../Pages/chessBoard.php?id=" + newGame["id"];
        }
      },
    });
  } catch (err) {
    window.alert(err.message);
  }
}
