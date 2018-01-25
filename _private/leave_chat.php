<?php
include_once('classes/chat.php');

if (isset($_GET['chat'])) {
	$chatid = round($_GET['chat']);
	$tryChat = new Chat($mysqli, $chatid);
	if (!$tryChat->getId()) {
		include_once('header2.php');
		errormsg("You are trying to leave a chat that doesn't exist.");
		backlink('Player page', 'index.php?page=pIndex');
	}
	else {
		if ($curChar->getCurrentChat()!=$tryChat->getId()) {
			include_once('header2.php');
			infomsg("You are trying to leave a different chat than the one you're in. Try again with the proper link.");
			$curChat = new Chat($mysqli, $curChatId);
			$curChat->printLeaveLink($curCharid);
			backlink('Character page', 'index.php?page=cIndex&charid=' . $curCharid);
		}
		else {
			$check = $curChar->leave($chatid);
			if (!$check) {
				include_once('header2.php');
				infomsg("Unfortunately, leaving the chat failed. Sorry about that.");
				backlink('Character page', 'index.php?page=cIndex&charid=' . $curCharid);
			}
			else {
				customRedirect('index.php?page=cIndex&charid=' . $curCharid, 'Character page');
			}
		}
	}
}
?>