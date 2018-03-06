<?php
include_once('constants.php');
include_once('header2.php');
if ($player->checkRight(R_VIEWLOG, YES)) {
	include_once('generic.php');//contains get activity log
	ptag('h1', 'User activity log');
	
	if (isset($_GET['limit'])) {
		if (is_numeric($_GET['limit'])) $limit = round($_GET['limit']);
		else $limit=25;
	}
	else $limit=25;
	
	$log = getActivityLog($mysqli, $limit);
	
	starttag('form', '', array(
		'method' => 'get',
		'action' => 'index.php',
		'class' => 'narrow'));
	ptag('input' , '', array(
		'type' => 'hidden',
		'name' => 'page',
		'value' => 'latestLogins'));
	starttag('p');
	ptag("label", "Limit: ", array('for' => 'limit', 'class' => 'minwide'));
	ptag("input", '', array(
		'type' => 'number',
		'id' => 'limit',
		'name' => 'limit',
		'value' => $limit,
		'min' => '1',
		'max' => '200'));
	closetag('p');
	starttag('p');
	ptag('button', 'Limit rows', array(
		'type' => 'submit',
		'id' => 'submit_btn',
		'class' => 'btn btn-primary'));
	closetag('p');
	closetag('form');
	
	if (is_array($log)) {
		starttag('ul');
		foreach ($log as $entry) {
			$p = new User($mysqli, array('uid' => $entry['user']));
			starttag('li');
			ptag('a', $p->getUsername(), array('href' => 'index.php?page=viewPlayer&ouser=' . $entry['user']));
			echo ' (id ' . $entry['user'] . ') logged in at ' . $entry['time'];
			closetag('li');
		}
		closetag('ul');
	}
	else para("Nothing to show.");
	
	backlink('Player Index', 'index.php?page=pIndex');
}
else {
	para("Unauthorized access.");
}
?>
