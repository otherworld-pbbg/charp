<?php
session_start();
/*
This page is just a wrapper for everything else.

The page that is currently being viewed will be attached through pages.php.

Any page that has text will attach the header.

This page does not attach the header because there might be pages that
redirect via header and it would fail if the page printed text before it was
called.
*/

define('PRIV_PATH', '../_private/');//note that unlike in earlier versions, this also includes the last backslash, so don't double it
require_once(PRIV_PATH . "constants.php");
require_once(PRIV_PATH . "conn_details.php");//defines host, dbname, user, pass, also defines BASE_URL
require_once(PRIV_PATH . "tags_printer.php");//html generator

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
	<title>Otherworld</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta property="og:title" content="Otherworld PBBG" />
	<meta property="og:description" content="A blob under construction." />
	
	<link rel="stylesheet" href="bootstrap.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
	<?php
	echo "<link rel='icon' type='image/x-icon' href='" . BASE_URL . "/favicon.ico'/>"
	?>
	</head>
	<body>
	<nav class="navbar navbar-dark bg-dark">
	<div class='container-fluid'>
	<div class="navbar-header">
	<?php
	ptag ("a", "Otherworld", array('href' => 'index.php', 'class' => 'navbar-brand'));
	closetag("div");
	starttag('ul', "", array('class' => 'nav navbar-nav navbar-right'));
	ptag ("li", " Register", array('style' => 'text-decoration: line-through'));
	ptag ("li", " Login", array('style' => 'text-decoration: line-through'));
	closetag("ul");
	closetag("div");
	closetag("nav");
	?>
	<div class='container-fluid'>
	<?php
	para("Unfortunately we couldn't establish a database connection, so the site cannot function. Sorry for the inconvenience. Please check back later.");
}
else {
	require_once(PRIV_PATH . "pages.php");
}
?>
</div>
</body>
</html>
