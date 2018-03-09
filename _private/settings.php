<?php
//Settings page

if (isset($_POST["password"])&&isset($_POST["email"]))
{
	if (password_verify($_POST["password"], $player->getPasshash())) {
		
		$email = $mysqli->real_escape_string($_POST["email"]);
		$check = generateActivationCode($mysqli, $_SESSION['logged_user'], $email, $player->getPasshash(), 2, $player->getId());
		if (is_a($check, "CustomError")) {
			include_once "header2.php";
			errormsg($check->getMsg());
		}
		else {
			include_once "header2.php";
			para("Activation code was sent to the email you provided. Follow the instructions in the email. If the email doesn't come through, you can have it resent. It might go in the spam folder, so check there first. Not that it might take a few minutes to come through, especially on gmail.");
		}
	}
	else header('Location: index.php?page=login&msg=no_login');
}

include_once "header2.php";
ptag("h1", "Player settings");
ptag("h2", "Update email");
starttag('form', '', array(
	'action' => 'index.php?page=pSettings',
	'method' => 'post',
	'class' => 'narrow'
));
para("Current email: " . $player->getEmail());
starttag('p');
ptag("label", "New email: ", array('for' => 'email', 'class' => 'minwide'));
ptag("input", "", array(
	'type' => 'email',
	'id' => 'email',
	'name' => 'email',
	'size' => 30,
	'maxlength' =>60
));
closetag('p');
starttag('p');
ptag("label", "Current password: ", array('for' => 'password', 'class' => 'minwide'));
ptag("input", "", array(
	'type' => 'password',
	'id' => 'password',
	'name' => 'password',
	'size' => 20,
	'maxlength' => 32));
closetag('p');
starttag('p');
ptag("input", "", array(
	'type' => 'submit',
	'id' => 'submit_btn',
	'name' => 'submit_btn',
	'value' => 'Send activation code'
	));
closetag('p');
para("You need to actually have access to the email address you're entering here. If it belongs to someone else, they can enter the activation code and reset the password to override it with their own, effectively taking over your account.");
closetag('form');

para("If you want to reset your password, go to index.php?page=reset");

backlink('Player page', 'index.php?page=pIndex');
?>
