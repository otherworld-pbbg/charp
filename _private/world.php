<?php
require_once('classes/world.php');
require_once('classes/location.php');
include_once('header2.php');
backlink('Player page', 'index.php?page=pIndex');
ptag('h1', 'The world');
$world = new World($mysqli);
$continents = $world->getLocsByType(1);
if (!$continents) {
	para("There are no continents yet. Check back later.");
}
else {
	foreach($continents as $co) {
		ptag('h2', $co->getName());
		para($co->getDescription());
		$co->printChildren();
	}
}
backlink('Player page', 'index.php?page=pIndex');
?>
