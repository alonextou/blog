var app = require('express')();
var server = require('http').createServer(app);
var io = require('socket.io').listen(server, {
	log: false
});

var fs = require('fs');

server.listen(3000);

var players = [];
var cards = fs.readFileSync('./cards.json');
cards = JSON.parse(cards);

cards.forEach(function(card){
	//console.log(card.name);
});

app.get('/', function (req, res) {
	res.sendfile(__dirname + '/index.html');
});

var gameInProgress = false;
var countDeck = 0;
var playerTurn = 0;

io.sockets.on('connection', function (socket) {

	// player join
	console.log('connect');
	socket.emit('getPlayer');
	socket.on('setPlayer', function(player){
		player.id = socket.id;
		player.turn = getPlayerCount(players) + 1;
		players[socket.id] = player;
		console.log(players);
		io.sockets.emit('updatePlayerCount', getPlayerCount(players));
	});

	// player leave
	socket.on('disconnect', function() {
		delete players[socket.id]
		var count = 1;
		for (i in players){
			players[i].turn = count;
			count++;
		}
		console.log(players);
		io.sockets.emit('updatePlayerCount', getPlayerCount(players));
	});

	// new game
	socket.on('newGame', function() {
		if(gameInProgress === true){
			console.log('Game already in progress');
		} else {
			console.log(players);
			console.log('Starting new game');
			gameInProgress = true;
			deck = shuffle(cards);

			var loopDeck = function() {
				var card = deck[countDeck];
				for(var i in players) {
					console.log(players[i]);
					console.log(playerTurn);
					if(players[i].turn === playerTurn + 1){
						var id = players[i].id;
						io.sockets.socket(id).emit('yourTurn', card);
						socket.broadcast.emit('newTurn', players[i]);
					}
				}
			}

			socket.on('turnDone', function() {
				countDeck++;
				playerTurn++;
				console.log(playerTurn);
				if (playerTurn === getPlayerCount(players)) {
					playerTurn = 0;
				}
				loopDeck();
			});
		}
	});


});

function getPlayerCount(players) {
  var count = 0;
  for(var player in players) {
  	count++;
  }
  return count;
}

//+ Jonas Raoni Soares Silva
//@ http://jsfromhell.com/array/shuffle [v1.0]
function shuffle(o){ //v1.0
    for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
};