<?php
include_once('header2.php');
include_once('classes/location.php');
if (isset($_GET['msg'])) interpretMsg($_GET['msg']);

ptag('h1', 'Player Index');
$lcount = $player->countLogins();
if ($lcount==1) {
	para("Hi there! Since you're new, I thought I'd welcome you to the game. This is still in development, so there might not be much to see, but I hope you'll come back when it's finished.");
}
else para("Welcome back. You have logged in " . $lcount . " times so far.");
starttag('div', '', array('class' => 'row'));
	starttag('div', '', array('class' => 'col-md-6'));
		starttag('div', '', array('class' => 'panel'));
		$charlist = $player->getCharacters();
		if (!$charlist) {
			starttag('p', "You don't have any characters at the moment. Would you like to ");
			ptag('a', 'create one', array('href' => 'index.php?page=newChar'));
			echo '?';
			closetag('p');
		}
		else {
			$charids = array();
			
			ptag('h2', 'List of characters');
			starttag('ul');
			foreach($charlist as $c) {
				$charids[] = $c->getId();
				$cmemo = $player->getMemo($c->getId());
				$pl = new Location($c->getLocation());
				$locname = $pl->getName();
				starttag('li');
				ptag('a', $c->getName(), array(
					'href' => 'index.php?page=cIndex&charid=' . $c->getId(),
					'class' => 'minwide'
					));
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
						starttag('li', 'New msgs: ');
							ptag('span', '', array('id' => 'counter-' . $c->getId()));
						closetag('li');
					closetag('ul');
				closetag('li');
			}
			closetag('ul');
			?>
			<script>
			var characters = <?php echo json_encode($charids) ?>;
			$(document).ready(function(){
				$('[data-toggle="popover"]').popover();
				for (var i=0; i<characters.length; i++) {
					countNewEvents(characters[i]);
				}
				var seconds = 10;
				setInterval(refresh, 1000*seconds);
			});
			
			function updateCounter(contents, charId) {
				var countername = '#counter-' + charId;
				var display = $(countername);
				if (contents == '-1') contents = 'err';
				display.html(contents);
			}
					
			function refresh() {
				for (var i=0; i<characters.length; i++) {
					countNewEvents(characters[i]);
				}
			}
			
			function countNewEvents(charId) {
				$.ajax('get_messages.php', {
					'method': 'POST',
					'data': {'charId': charId, 'justcount': 'yes'},
					'success': function (response) {
						updateCounter(response, charId);
					}
				});
			}
			</script>
			<?php
		}
		closetag('div');//end panel
	closetag('div');//endcol
	starttag('div', '', array('class' => 'col-md-6'));
		starttag('div', '', array('class' => 'panel'));
			starttag('div', '', array('class' => 'panel-heading'));
				ptag('h3', 'Player controls');
			closetag('div');
			starttag('div', '', array('class' => 'panel-body'));
				ptag('a', 'Create new character', array('href' => 'index.php?page=newChar'));
			closetag('div');//end panel body
			starttag('div', '', array('class' => 'panel-body'));
				ptag('a', 'Familiarize yourself with the world', array('href' => 'index.php?page=worldInfo'));
			closetag('div');//end panel body

			if ($player->checkRight(R_VIEWLOG, YES)) {
				starttag('div', '', array('class' => 'panel-body'));
					ptag('a', 'View latest logins', array('href' => 'index.php?page=latestLogins'));
				closetag('div');//end panel body
			}
			closetag('div');//end panel body
		closetag('div');//end panel
	closetag('div');//endcol
closetag('div');//endrow
?>
