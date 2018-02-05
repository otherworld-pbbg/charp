<!DOCTYPE html>
<!-- This header is only attached when logged in -->
<html lang="en">
<head>
<title>Otherworld</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:title" content="Otherworld PBBG" />
<?php
if (isset($_GET["page"])) {
	if ($_GET["page"]=="status") echo '<meta property="og:description" content="A story of what has been done" />';
	else echo '<meta property="og:description" content="See the world from a new perspective - or a few." />';
}
else echo '<meta property="og:description" content="A blob under construction." />';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
<script src="popper.min.js"></script>
<script src="bootstrap.min.js"></script>
<script src="minimize_text.js"></script>
<link rel="stylesheet" href="bootstrap.css">
<link rel="stylesheet" href="game_specific.css">
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
if (!isset($player)) ptag ('li', 'If you see this, report to administration');
else ptag ('li', 'Logged in as ' . $player->getUsername());//player is always set in pages if login is valid
//There will be a logout link here eventually
closetag("ul");
closetag("div");
closetag("nav");
?>
<div class='container-fluid'>
