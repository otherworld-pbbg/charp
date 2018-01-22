<?php
define('PRIV_PATH', '../_private/');
require_once(PRIV_PATH . "conn_details.php");//defines host, dbname, user, pass, also defines BASE_URL
require_once(PRIV_PATH . "tags_printer.php");//html generator
include_once(PRIV_PATH . "generic.php");

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
	include_once(PRIV_PATH . 'header.php');
	para("Sorry, there is no database connection, so it can't compare your activation string.");
}
else {

$displayForm = true;

if (isset($_POST["username"])&&isset($_POST["activation"])&&isset($_POST["type2"]))
{
	if (is_numeric($_POST["type2"])) {
		$type = $_POST["type2"];
		$activation = $mysqli->real_escape_string($_POST["activation"]);
		if (isUsername($_POST["username"]))
		{
			$uname = $_POST["username"];
			$check = checkPending($mysqli, $uname);
			
			if ($check) {
				if ($type == 1) {
					$check2 = activateAccount($mysqli, $uname, $activation);
					if (is_a($check2, "CustomError")) {
						include_once(PRIV_PATH . 'header.php');
						errormsg($check2->getMsg());
					}
					else if (is_a($check2, "CustomDisclaimer")) {
						include_once(PRIV_PATH . 'header.php');
						infomsg($check2->getMsg());
						para("Now you can log in at the <a href='index.php?page=login'>login page</a>.");
						$displayForm = false;
					}
					else {
						include_once(PRIV_PATH . 'header.php');
						para("Your account was registered successfully!");
						para("Now you can log in at the <a href='index.php?page=login'>login page</a>.");
						$displayForm = false;
					}
				}
				else if ($type == 2) {
					$check2 = activateEmail($mysqli, $uname, $activation);
					if (is_a($check2, "CustomError")) {
						include_once(PRIV_PATH . 'header.php');
						errormsg($check2->getMsg());
					}
					else if (is_a($check2, "CustomDisclaimer")) {
						include_once(PRIV_PATH . 'header.php');
						infomsg($check2->getMsg());
						$displayForm = false;
					}
					else {
						include_once(PRIV_PATH . 'header.php');
						para("Your email was changed successfully!");
						$displayForm = false;
					}
				}
				else if ($type == 3) {
					if (isset($_POST["password"])) {
						$pw = password_hash($_POST["password"], PASSWORD_DEFAULT);
						$check2 = resetPassword($mysqli, $uname, $activation, $pw);
						if (is_a($check2, "CustomError")) {
							include_once(PRIV_PATH . 'header.php');
							errormsg($check2->getMsg());
						}
						else if (is_a($check2, "CustomDisclaimer")) {
							include_once(PRIV_PATH . 'header.php');
							infomsg($check2->getMsg());
							para("Now you can log in with your new password at the <a href='index.php?page=login'>login page</a>.");
							$displayForm = false;
						}
						else {
							include_once(PRIV_PATH . 'header.php');
							para("Your password was changed successfully!");
							para("Now you can log in with your new password at the <a href='index.php?page=login'>login page</a>.");
							$displayForm = false;
						}
					}
					else {
						include_once(PRIV_PATH . 'header.php');
						para("You can't change your password to blank. You have to enter at least something.");
					}
				}
				else {
					include_once(PRIV_PATH . 'header.php');
					para("Apparently you somehow managed to enter an invalid activation type. You need to select one of the radio buttons.");
				}
			}
			else {
				include_once(PRIV_PATH . 'header.php');
				para("There is no pending activation concerning the given username. Make sure that the username wasn't misspelled.");
			}
		}
		else {
			include_once(PRIV_PATH . 'header.php');
			para("The username was invalid! Only alphanumeric characters and the underscore are allowed. Try again.");
		}
		
	}
}
if ($displayForm)
{
	include_once(PRIV_PATH . 'header.php');
	ptag("h2", "Enter activation code");
	starttag('form', '', array(
		'action'=>'activate.php',
		'method'=>'post',
		'class'=>'narrow'));
	ptag("h3", "Type of activation");
	starttag('p');
	ptag("input", "", array('type' => 'radio', 'id' => 'type1', 'name' => 'type2', 'value' => '1', 'checked' => 'checked'));
	ptag("label", "New account", array('for' => 'type1'));
	closetag('p');
	starttag('p');
	ptag("input", "", array('type' => 'radio', 'id' => 'type2', 'name' => 'type2', 'value' => '2'));
	ptag("label", "New email", array('for' => 'type2'));
	closetag('p');
	starttag('p');
	ptag("input", "", array('type' => 'radio', 'id' => 'type3', 'name' => 'type2', 'value' => '3'));
	ptag("label", "New password", array('for' => 'type3'));
	closetag('p');
	starttag('p');
	ptag("label", "Username: ", array('for' => 'username'));
	ptag("input", "", array('' => 'text', 'id' => 'username', 'name' => 'username', 'size' => 20, 'maxlength' => 20));
	closetag('p');
	starttag('p');
	ptag("label", "New password*: ", array('for' => 'password'));
	ptag("input", "", array('type' => 'password', 'id' => 'password', 'name' => 'password', 'size' => 20, 'maxlength' => 20));
	closetag('p');
	para("*) Only necessary if you are resetting your password, otherwise irrelevant.");
	starttag('p');
	ptag("label", "Activation code: ", array('for' => 'activation'));
	ptag("input", "", array('type' => 'text', 'id' => 'activation', 'name' => 'activation', 'size' => 30, 'maxlength' => 60));
	closetag('p');
	starttag('p');
	ptag("input", "", array('type' => 'submit', 'id' => 'submit_btn', 'name' => 'submit_btn', 'value' => 'Activate'));
	closetag('p');
	closetag('form');
}

include(PRIV_PATH . 'resend.php');//The resend activation code form
}
?>
</body>
</html>
