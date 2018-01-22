<?php
include_once('header2.php');
backlink('Player page', 'index.php?page=pIndex');
			
if (!$curChar->getLocation()) {
	para("You are currently in the Limbo, which is the space between locations. In order to enter the world, you need to select a starting location. After this, you are limited by the rules of transit of the world, and can no longer cross long distances in an instant.");
}

include_once('show_chat.php');

?>