<!DOCTYPE html>
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

<link rel="stylesheet" href="bootstrap.css">
<link rel="stylesheet" href="game_specific.css">
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
ptag ("li", "<a href='index.php?page=register'><span class='glyphicon glyphicon-user'></span> Register</a>");
ptag ("li", "<a href='index.php?page=login'><span class='glyphicon glyphicon-log-in'></span> Login</a>");
closetag("ul");
closetag("div");
closetag("nav");
?>
<div class='container-fluid'>
