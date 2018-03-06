<?php
//Admin ability to see who another user plays
//to do: add ability to add and edit memos
//This might later include the ability to kill off characters but it's not a priority
include_once('constants.php');
include_once('classes/user.php');
include_once('classes/character.php');
include_once('classes/location.php');

include_once('header2.php');
if (!$player->checkRight(R_VIEW_OTHERS, YES)) {
	para("You aren't authorized to view this page.");
}
else {
	if (!isset($_GET["ouser"])) {
		para('Id of other user not defined.');
	}
	else {
		$oUser = round($_GET["ouser"]);
		$player2 = new User($mysqli, array('uid' => $oUser));
		
		if (!$player2->getId()) {
			para("$oUser is not a valid user id. No entry found.");
		}
		else {
			ptag('h2', 'Viewing another user');
			$charlist = $player2->getCharacters();
			if (!$charlist) {
				starttag('p', $player2->getUsername() . " (id: $oUser) doesn't have any characters.");
			}
			else {
				?>
				<script>
				$(document).ready(function(){
					$('[data-toggle="popover"]').popover(); 
				});
				</script>
				<?php
				ptag ("h3", "Characters for " . $player2->getUsername() . " (id " . $oUser . ")");
				starttag('ul');
				foreach($charlist as $c) {
					$cmemo = $player->getMemo($c->getId());//Note that this memo is from your perspective and not the user's you are viewing
					$pl = new Location($mysqli, array(
						'uid' => $c->getLocation()
						));
					$locname = $pl->getName();
					starttag('li', $c->getName());
					if ($cmemo) {
					ptag('button', 'memo', array(
						'type' => 'button',
						'class' => 'btn btn-secondary btn-sm',
						'title' => 'memo',
						'data-container' => 'body',
						'data-toggle' => 'popover',
						'data-placement' => 'right',
						'data-content' => $cmemo['contents'],
						'data-original-title' => 'memo'
						));
					}
						starttag('ul');
							ptag('li', 'Loc: ' . $locname);
						closetag('ul');
					closetag('li');
				}
				closetag('ul');
			}
		}

		backlink('Latest Logins', 'index.php?page=latestLogins');
	}
}
backlink('Player Index', 'index.php?page=pIndex')
?>
