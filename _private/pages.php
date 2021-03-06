<?php
include_once('generic.php');
include_once('classes/user.php');
include_once('classes/character.php');

//list of page handles and corresponding links will be here
$pages_ext = array(
	//pages that don't require you to be logged in
	"login" => "login.php",
	"register" => "register.php",//register new account
	"reset" => "reset.php",//reset password
	"resend" => "resend_handler.php",
	"history" => "history.php",
	"status" => "history.php"//for legacy reasons because there might be old links that point here
	);
$pages_loggedin = array(
	//logged in
	'pIndex' => 'player_index.php',
	'pSettings' => 'settings.php',
	'newChar' => 'new_char.php',
	'createChar' => 'create_char.php',
	'worldInfo' => 'world.php',
	'latestLogins' => 'latest_logins.php',
	'viewPlayer' => 'view_player.php'
	);
$pages_char = array(
	//character specific actions, requires character id
	"cIndex" => "char_index.php",
	'changeName' => 'change_name.php',
	'changeDesc' => 'change_desc.php',
	'joinChat' => 'join_chat.php',
	'leaveChat' => 'leave_chat.php',
	'spawn' => 'spawn.php'
);

$page = false;
if (isset($_GET["page"])) $page = $_GET["page"];//note, don't use page in queries because it's not sanitized
else {
	include(PRIV_PATH . "frontpage.php");
}

if ($page)
{
	if (isset($pages_ext[$page]))
	{
		include_once (dirname(__FILE__) . "/" . $pages_ext[$page]);
	}
	else if (isset($pages_loggedin[$page]))
	{
		//check validity of login
		if (!isset($_SESSION['user_id'])||$_SESSION['user_id']==0) header('Location: index.php?page=login&msg=no_login');
		else {
			$player = new User($mysqli, array('uid' => $_SESSION['user_id']));
			if ($player->getId()==0) header('Location: index.php?page=login&msg=no_login');//precaution
			//user id should only be placed in the session variable at login if it's valid
			include_once (dirname(__FILE__) . "/" . $pages_loggedin[$page]);
		}
	}
	else if (isset($pages_char[$page]))
	{
		//check validity of login and char
		if (!isset($_SESSION['user_id'])||$_SESSION['user_id']==0) header('Location: index.php?page=login&msg=no_login');
		else {
			$player = new User($mysqli, array('uid' => $_SESSION['user_id']));
			if ($player->getId()==0) header('Location: index.php?page=login&msg=no_login');//precaution
			if (!isset($_REQUEST['charid'])) header('Location: index.php?page=pIndex&msg=no_charid');
			else {
				$curCharid = round($_REQUEST['charid']);//this can be either post or get depending on page
				//rounding to get rid of possible decimals and other invalid content
				$curChar = new Character($mysqli, array('uid' => $curCharid));
				if (!$curChar->getId()) header('Location: index.php?page=pIndex&msg=invalid_char');//Id gets set as 0 if loading fails
				else if ($curChar->getOwner()!=$_SESSION['user_id']) header('Location: index.php?page=pIndex&msg=not_yours');
				else include_once (dirname(__FILE__) . "/" . $pages_char[$page]);
			}
		}
	}
	else
	{
		para("Unknown page \"$page\".");
	}
}

?>