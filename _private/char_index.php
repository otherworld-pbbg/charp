<?php
include_once('header2.php');
include_once('classes/chat.php');
include_once('classes/location.php');
include_once('classes/world.php');
if (isset($_GET['msg'])) interpretMsg($_GET['msg']);
backlink('Player page', 'index.php?page=pIndex');

starttag('div', '', array('class' => 'row'));
starttag('div', '', array('class' => 'col-lg-4'));
starttag('div', '', array('class' => 'panel'));
starttag('form', '', array(
	'action' => 'index.php?page=changeName&charid=' . $curCharid,
	'method' => 'post',
	'id' => 'editName',
	'style' => 'display: none'
	));
ptag('input', '', array(
	'type' => 'text',
	'name' => 'cname',
	'value' => $curChar->getName()
	));
ptag('button', 'Save', array(
	'type' => 'submit',
	'class' => 'btn btn-secondary'
	));
closetag('form');
starttag('p', 'Current character: ' . $curChar->getName());
ptag('button', 'Change', array(
	'type' => 'button',
	'id' => 'toggle',
	'class' => 'btn btn-secondary btn-sm align-text-bottom',
	'onClick' => 'toggleForm(1)'
	));
closetag('p');
$desc = $curChar->getDescription();
if (!$desc) {
	$oldval = '';
}
else {
	$oldval = $desc['contents'];
	
}
starttag('form', '', array(
	'action' => 'index.php?page=changeDesc&charid=' . $curCharid,
	'method' => 'post',
	'id' => 'editDesc',
	'style' => 'display: none'
	));
ptag('textarea', $oldval, array(
	'cols' => 80,
	'rows' => 3,
	'name' => 'desc'
	));
ptag('button', 'Save', array(
	'type' => 'submit',
	'class' => 'btn btn-secondary'
	));
closetag('form');

if (!$desc) {
	ptag("p", "You don't have a description yet. Would you like to add one?");
}
else ptag('p', 'Description: ' . $oldval, array('class' => 'minimize'));

starttag('p');
ptag('button', 'Change', array(
	'type' => 'button',
	'id' => 'toggle2',
	'class' => 'btn btn-secondary btn-sm align-text-bottom',
	'onClick' => 'toggleForm(2)'
	));
closetag('p');

?>
<script>
function toggleForm(f) {
	if (f==1) {
		if (document.getElementById("editName").style.display=='none') {
			document.getElementById("editName").style.display='block';
			document.getElementById("toggle").innerHTML='Cancel';
		}
		else {
			document.getElementById("editName").style.display='none';
			document.getElementById("toggle").innerHTML='Change';
		}
	}
	if (f==2) {
		if (document.getElementById("editDesc").style.display=='none') {
			document.getElementById("editDesc").style.display='block';
			document.getElementById("toggle2").innerHTML='Cancel';
		}
		else {
			document.getElementById("editDesc").style.display='none';
			document.getElementById("toggle2").innerHTML='Change';
		}
	}
}


</script>
<?php

$curlocid = $curChar->getLocation();
$curLoc = new Location($mysqli, $curlocid);
if (!$curlocid) {
	para("You are currently in the Limbo, which is the space between locations. In order to enter the world, you need to select a starting location. After this, you are limited by the rules of transit of the world, and can no longer cross long distances in an instant.");
	//List possible starting locations
	$the_world = new World($mysqli);
	$possible_locations = $the_world->getStartingLocations();
	if (!$possible_locations) {
		para("There aren't any starting locations open at the moment. Check back later.");
	}
	else {
		ptag('h3', 'Open starting locations');
		foreach ($possible_locations as $pl) {
			starttag('form', '', array(
				'action' => 'index.php?page=spawn',
				'method' => 'post'
				));
			ptag('input', '', array(
				'type' => 'hidden',
				'name' => 'charid',
				'value' => $curChar->getId()
				));
			ptag('input', '', array(
				'type' => 'hidden',
				'name' => 'loc',
				'value' => $pl->getId()
				));
			ptag('h5', $pl->getName());
			para('Type: ' . $pl->interpretType());
			ptag('p', $pl->getDescription(), array('id' => 'locdesc-' . $pl->getId(), 'class' => 'minimize'));
			starttag('p');
			ptag('button', 'Start here', array(
				'type' => 'submit',
				'class' => 'btn btn-primary'
				));
			closetag('p');
			closetag('form');
		}
	}
}
else {
	para('Current location: ' . $curLoc->getName());
	ptag('p', 'Description: ' . $curLoc->getDescription(), array('class' => 'minimize'));
}
closetag('div');
closetag('div');
$curChatId = $curChar->getCurrentChat();
if (!$curChatId) {
	starttag('div', '', array('class' => 'col-lg-8'));
	starttag('div', '', array('class' => 'panel'));
	para("You're not currently in chat.");
	$possibleChats = $curLoc->getChats();
	if (!$possibleChats) {
		para('There are currently no chats to join in this location.');
	}
	else {
		ptag('h3', 'List of available chats');
		starttag('ul');
		foreach ($possibleChats as $pc) {
			starttag('li');
			ptag('h4', $pc->getName());
			starttag('ul');
			closetag('li');
			ptag('li', $pc->getSummary());
			$ppl = $pc->getParticipants();
			if (!$ppl) ptag('li', 'Current participants: none');
			else ptag('li', 'Current participants: ' . sizeof($ppl));
			starttag('li');
			$pc->printJoinLink($curCharid);
			closetag('li');
			closetag('ul');
		}
		closetag('ul');
	}
	closetag('div');
	closetag('div');
}
else {
	starttag('div', '', array('id' => 'middle-panel', 'class' => 'col-lg-6'));
	$curChat = new Chat($mysqli, $curChatId);
	ptag('h5', 'Current chat: ' . $curChat->getName());
	include_once('show_chat.php');
}
closetag('div');
backlink('Player page', 'index.php?page=pIndex');
?>