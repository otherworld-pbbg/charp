<?php
include_once("generic.php");

$displayForm = true;

if (isset($_POST["username"])&&isset($_POST["password"])&&isset($_POST["submit_btn"]))
{
	if (isUsername($_POST["username"]))
	{
		$uname = $_POST["username"];//since any special characters make the string disqualified, it doesn't need to be separately sanitized
		$info = getExistingAccount($mysqli, $uname);
		if (is_a($info, "CustomError")) {
			include_once "header.php";
			errormsg($info->getMsg());
		}
		else {
			include_once "classes/user.php";
			if (password_verify($_POST["password"], $info['passhash2'])) {
				$_SESSION['logged_user'] = $_POST['username'];
				$_SESSION['user_id'] = $info['uid'];
				$player = new User($mysqli, array('uid' => $info['uid']));
				$player->logLogin();
				
				header('Location: index.php?page=pIndex');
				$displayForm = false;
			}
			else {
				include_once "header.php";
				starttag('div', '', array('class' => 'alert alert-warning'));
				para("Wrong password!");
				para("If you have a valid email account associated with your Otherworld account, you can go to <a href='index.php?page=reset'>this page</a> to have your password reset. However, if your account was created before we started requiring a valid email, you need to contact admin for a manual reset. After you get to your account, be sure to update your email address if it's not already up to date, so that if you forget your password again, you can reset it any time. Basically if you haven't logged in since 2017-09-04, your password no longer works.");
				closetag('div');
			}
			
		}
	}
	else {
		include_once "header.php";
		para("The username was invalid! Only alphanumeric characters and the underscore are allowed.");
	}
}
if (isset($_GET['msg'])) interpretMsg($_GET['msg']);
if ($displayForm)
{
	include_once "header.php";
	starttag("form", "", array(
		'action' => 'index.php?page=login',
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
	ptag("input", "", array(
		'type' => 'submit',
		'id' => 'submit_btn',
		'name' => 'submit_btn',
		'value' => 'Log in'));
	closetag("p");
	
	ptag("a", "[Forgot password?]", array(
		'href' => 'index.php?page=reset',
		'class' => 'smaller'));
	closetag("form");
}

?>
