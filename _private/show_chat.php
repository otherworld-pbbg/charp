<?php
include_once('header2.php');
include_once('classes/chat.php');
backlink('Player page', 'index.php?page=pIndex');

?>
<div id="chat" style="width: 100%">
</div>
<input type='text' size='100' id='saybox' onkeypress='return enter(event)'>
<input type='button' id='say_btn' value='Add'>

<script>
var charId = <?php echo $curChar->getId() ?>;

window.onload = function() {
	
	fetchOldEvents(charId);
	var seconds = 4;
	setInterval(refresh, 1000*seconds);
}

function enter(e) {
    if (e.keyCode == 13) {
        var msg = $('#saybox').val();
		if (msg != "") {
			sendMsg(charId, msg);
		}
		$('#saybox').val("");
    }
}

$('#say_btn').on('click', function(event) {
	var msg = $('#saybox').val();
	if (msg != "") {
		sendMsg(charId, msg);
	}
	$('#saybox').val("");
} );

function updateChat(stuffToAdd) {
	var displayConsole = $('#chat');
	var messageToDisplay = stuffToAdd;
	displayConsole.append(messageToDisplay);
}
		
function refresh() {
	var charId = <?php echo $curChar->getId() ?>;
	fetchNewEvents(charId);
}

function fetchOldEvents(charId) {
	$.ajax('get_messages.php', {
		'method': 'POST',
		'data': {'charId': charId, 'lastseen': 0},
		'success': function (response) {
			updateChat(response);
		}
	});
}

function fetchNewEvents(charId) {
	$.ajax('get_messages.php', {
		'method': 'POST',
		'data': {'charId': charId},
		'success': function (response) {
			updateChat(response);
		}
	});
}

function sendMsg(charId, msg) {
	$.ajax('get_messages.php', {
		'method': 'POST',
		'data': {'charId': charId, 'msg': msg},
		'success': function (response) {
			updateChat(response);
		}
	});
}
</script>
