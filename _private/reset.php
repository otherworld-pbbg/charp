<?php
include_once("generic.php");
require_once("tags_printer.php");//errormsg, infomsg function
$displayForm = true;

if (isset($_POST["username"])&&isset($_POST["email"]))
{
	if (isUsername($_POST["username"]))
	{
		$uname = $_POST["username"];
		$email = $mysqli->real_escape_string($_POST["email"]);
		
		$info = getExistingAccount($mysqli, $uname);
		if (is_a($info, "CustomError")) {
			include_once "header.php";
			errormsg($info->getMsg());
		}
		else {
			$check = generateActivationCode($mysqli, $uname, $email, "being changed", 3, $info->uid);
			if (is_a($check, 'CustomError')) {
				include_once "header.php";
				errormsg($check->getMsg());
			}
			else {
				include_once "header.inc.php";
				infomsg("Activation code was sent to the email you provided. Follow the instructions in the email. If the email doesn't come through, you can have it resent. It might go in the spam folder, so check there first.");
				$displayForm = false;
			}
		}
	}
	else {
		include_once "header.inc.php";
		errormsg("The username was invalid! Only alphanumeric characters and the underscore are allowed. Try again.");
	}
}
if ($displayForm)
{
	include_once "header.php";
	ptag("h1", "Reset your password");
	para("You need to know both your username and the email address associated with the account, otherwise we cannot confirm that it's actually yours. You will be prompted for a new password after using the code that will be emailed to you.");
	para("If you registered before a valid email address was required, you need to contact admin for a manual reset.");
	starttag('form', '', array(
		'action' => 'index.php?page=reset',
		'method' => 'post',
		'class' => 'narrow'));
	starttag('p');
	ptag("label", "Username: ", array(
		'for' => 'username',
		'class' => 'minwide'));
	ptag("input", "", array(
		'type' => 'text',
		'id' => 'username',
		'name' => 'username',
		'size' => 20,
		'maxlength' => 20));
	closetag('p');
	starttag('p');
	ptag("label", "Email: ", array(
		'for' => 'email',
		'class' => 'minwide'));
	ptag("input", "", array(
		'type' => 'text',
		'id' => 'email',
		'name' => 'email',
		'size' => 30,
		'maxlength' => 60));
	closetag('p');
	starttag('p');
	ptag("input", "", array(
		'type' => 'submit',
		'id' => 'submit_btn',
		'name' => 'submit_btn',
		'value' => 'Reset'));
	closetag('p');
	starttag('div', '', array('class' => 'alert alert-info'));
	para("Disclaimers:");
	para("1) Passwords are case sensitive, usernames are not.");
	para("2) You are responsible for all activities that happen on your account, so if you pick an easy password and someone guesses it, you can be held responsible for anything they do on your account. It's recommended that the password is at least 7 characters and contains at least one letter and number. It's up to you if you comply or not.");
	closetag('div');
	closetag('form');
}

include('resend.php');//The resend activation code form
?>