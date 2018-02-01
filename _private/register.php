<?php
include_once("generic.php");
include_once("dictionary.php");
require_once("tags_printer.php");//errormsg, infomsg function
$displayForm = true;

if (isset($_POST["username"])&&isset($_POST["password"])&&isset($_POST["email"])&&isset($_POST["submit_btn"]))
{
	if (isUsername($_POST["username"]))
	{
		$uname = $_POST["username"];
		$check = checkFreeUsername($mysqli, $uname);
		if (is_a($check, 'CustomError')) {
			include_once "header.php";
			infomsg($check->getMsg());
		}
		else {
			$passhash = password_hash($_POST["password"], PASSWORD_DEFAULT);
			$email = $mysqli->real_escape_string($_POST["email"]);
			$check2 = generateActivationCode($mysqli, $uname, $email, $passhash);
			if (is_a($check, 'CustomError')) {
				include_once "header.php";
				errormsg($check->getMsg());
			}
			else {
				include_once "header.php";
				infomsg("Activation code was sent to the email you provided. Follow the instructions in the email. If the email doesn't come through, you can have it resent. It might go in the spam folder, so check there first.");
				$displayForm = false;
			}
		}
	}
	else {
		include_once('header.php');
		errormsg("The username was invalid! Only alphanumeric characters and the underscore are allowed. Try again.");
	}
}
if ($displayForm)
{
	include_once('header.php');
	ptag("h1", "Register new account");
	para("Register only once. If the activation code doesn't come through, ask to have it resent. Only if it's been over 24 hours, you will have to try again.");
	starttag("form", "", array(
		'action' => 'index.php?page=register',
		'method' => 'post',
		'class' => 'narrow'));
	
	starttag("p");
	ptag("label", "Username: ", array('for' => 'username', 'class' => 'minwide'));
	ptag("input", "", array(
		'type' => 'text',
		'id' => 'username',
		'name' => 'username',
		'size' => 20,
		'maxlength' => 20));
	closetag("p");
	
	starttag("p");
	ptag("label", "Password: ", array('for' => 'password', 'class' => 'minwide'));
	ptag("input", "", array(
		'type' => 'password',
		'id' => 'password',
		'name' => 'password',
		'size' => 20,
		'maxlength' => 32));
	closetag("p");
	
	starttag("p");
	ptag("label", "Email: ", array('for' => 'email', 'class' => 'minwide'));
	ptag("input", "", array(
		'type' => 'text',
		'id' => 'email',
		'name' => 'email',
		'size' => 30,
		'maxlength' => 60));
	closetag("p");
	
	starttag("p");
	ptag("input", "", array(
		'type' => 'submit',
		'id' => 'submit_btn',
		'name' => 'submit_btn',
		'value' => 'Register'));
	closetag("p");
	
	echo '<div class="alert alert-info">';
	para("Disclaimers:"); 
	para("1) Usernames shouldn't use any special characters (underscores are allowed though).");
	para("2) Passwords are case sensitive, usernames are not.");
	para("3) You are responsible for all activities that happen on your account, so if you pick an easy password and someone guesses it, you can be held responsible for anything they do on your account. It's recommended that the password is at least 7 characters and contains at least one letter and number. It's up to you if you comply or not.");
	echo "</div>";
	echo "</form>";
}

include('resend.php');//The resend activation code form
?>
