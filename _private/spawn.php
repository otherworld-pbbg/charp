<?php
if (!isset($_POST['loc'])) {
		customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=missing_data', 'Character page');
}
else {
	if (!is_numeric($_POST['loc'])) {
		customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=invalid_data', 'Character page');
	}
	else if ($curChar->getLocation()) {
		customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=already_started', 'Character page');
	}
	else {
		$try = round($_POST['loc']);//eliminates decimal numbers
		require_once('classes/location.php');
		$testloc = new Location($mysqli, $try);
		$targetid = $testloc->getId();//It sets it to 0 if fetch from db failed
		if (!$targetid) customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=invalid_data', 'Character page');
		else if (!$testloc->getSpawning()) {
			customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=invalid_spawning_location', 'Character page');
		}
		else {
			$check = $curChar->spawn($targetid);
			if (is_a($check, 'CustomError')) {
				include_once "header2.php";
				errormsg($check->getMsg());
				backlink('Character page', 'index.php?page=cIndex&charid=' . $curCharid);
			}
			else {
				$curChatId = $curChar->getCurrentChat();
				if ($curChatId) $curChar->leave($curChatId);//I don't think it's necessary to print an error message if leaving chat fails, since it's unlikely
				customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=spawn_success', 'Character page');
			}
		}
	}
}
?>
