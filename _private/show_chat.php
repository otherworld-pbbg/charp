<?php
include_once('header2.php');
include_once('classes/chat.php');
para("Notice: Autoscroll is only active when the say box has focus, so if you want to read older messages without autoscroll, click outside the textbox.");
?>

<div id="chat" class='chat'>
</div>
<input type='text' size='70' id='saybox' onkeypress='return enter(event)'>
<input type='button' id='say_btn' value='Add' class='btn btn-primary'>
<?php
ptag('button', 'Hide list', array(
	'type' => 'button',
	'id' => 'toggle-side',
	'class' => 'btn btn-secondary',
	'onClick' => 'toggleSidePanel()'
	));
starttag('p');
$curChat->printLeaveLink($curCharid);
closetag('p');
?>
</div>
<div class='col-lg-2' id='sidepane'>
<div class='panel-heading'>
<h4>Current participants</h4>
</div>
<div id='ppl_list' class='ppl_list panel-body'>
Not loaded.
</div>
</div>
</div>

<script>
var charId = <?php echo $curChar->getId() ?>;

window.onload = function() {
	
	fetchOldEvents(charId);
	fetchParticipants(charId);
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

function updatePpl(newContents) {
	var displayConsole = $('#ppl_list');
	displayConsole.html(newContents);
}
		
function refresh() {
	var charId = <?php echo $curChar->getId() ?>;
	fetchNewEvents(charId);
	fetchParticipants(charId);
	
	if (document.activeElement === document.getElementById('saybox')) $("#chat").scrollTop($("#chat")[0].scrollHeight);
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

function fetchParticipants(charId) {
	$.ajax('get_participants.php', {
		'method': 'POST',
		'data': {'charId': charId},
		'success': function (response) {
			updatePpl(response);
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

function toggleSidePanel() {
	if (document.getElementById('sidepane').style.display=='none') {
		document.getElementById('sidepane').style.display='inline-block';
		document.getElementById('toggle-side').innerHTML='Hide list';
		document.getElementById('middle-panel').className = "col-lg-6";
	}
	else {
		document.getElementById('sidepane').style.display='none';
		document.getElementById('toggle-side').innerHTML='Show list';
		document.getElementById('middle-panel').className = "col-lg-8";
	}
}
</script>
