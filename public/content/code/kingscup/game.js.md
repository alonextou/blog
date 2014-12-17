$(document).foundation();

$(document).ready(function() {

	var socket = io.connect('http://game.dev:3000');

	var players = [];
	var player = {};
	var playerCount;
	var currentPlayer;

	socket.on('getPlayer', function() {
		player.name = prompt("Please enter your name:","Yupi");
		$('#playerName').html(player.name);
		socket.emit('setPlayer', player);
	});

	socket.on('updatePlayerCount', function (count) {
		playerCount = count;
		$('#playerCount').html(playerCount);
	});

	$('#newGame').click(function(){
		socket.emit('newGame');
	});

	socket.on('newTurn', function (player) {
		console.log('new players turn: ' + player.name);
		$('#currentTurn').html(player.name);
	});

	socket.on('yourTurn', function (card) {
		$('#currentTurn').html('YOUR TURN, ' + player.name);
		$('#card').on('click', function(){
			$('#card').attr('src', '/images/cards/' + card.path);
			$('#cardName').html(card.name);
			$('#cardRule').html(card.rule);
			$('#card').off('click');
			socket.emit('turnDone');
		});
	});
	
});