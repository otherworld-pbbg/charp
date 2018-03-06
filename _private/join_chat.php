<?php
include_once('classes/chat.php');

if (isset($_GET['chat'])) {
	$chatid = round($_GET['chat']);
	$tryChat = new Chat($mysqli, array(
			'uid' => $chatid
		));
	if (!$tryChat->getId()) {
		include_once('header2.php');
		errormsg("You are trying to join a chat that doesn't exist.");
		backlink('Player page', 'index.php?page=pIndex');
	}
	else {
		if ($curChatId = $curChar->getCurrentChat()) {
			include_once('header2.php');
			infomsg("You are already in a chat, so you can't join another one without leaving the current one. You can either click to leave the current chat or return to the player page.");
			$curChat = new Chat($mysqli, array(
				'uid' => $curChatId
				));
			$curChat->printLeaveLink($curCharid);
			backlink('Character page', 'index.php?page=cIndex&charid=' . $curCharid);
		}
		else if ($tryChat->getLocation()!=$curChar->getLocation()) {
			include_once('header2.php');
			errormsg("You are trying to join a chat that is not in your location.");
			backlink('Character page', 'index.php?page=cIndex&charid=' . $curCharid);
		}
		else {
			$check = $curChar->join($tryChat);
			if (!$check) {
				include_once('header2.php');
				infomsg("Unfortunately, joining a chat failed. Sorry about that.");
				backlink('Character page', 'index.php?page=cIndex&charid=' . $curCharid);
			}
			else {
				customRedirect('index.php?page=cIndex&charid=' . $curCharid, 'Character page');
			}
		}
	}
}
?>