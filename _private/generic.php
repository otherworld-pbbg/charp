<?php
require_once("tags_printer.php");//errormsg, infomsg function
require_once("dictionary.php");
require_once("classes/custom_error.php");
require_once("classes/user.php");
require_once("classes/user_table.php");
require_once("classes/pending_users_table.php");
require_once("classes/activity_table.php");

function customRedirect($link, $pname) {
	header('Location: ' . $link);
	//to do: add alternative redirect here
	include_once('header2.php');
	para("Redirect failed. Click below to go where you were supposed to.");
	backlink($pname, $link);
}

function setBetween($val, $min, $max, $default) {
	if (!is_numeric($val)) return $default;
	if ($val<$min) return $min;
	if ($val>$max) return $max;
	return $val;
}

function setBint($val, $min, $max, $default) {
	if (!is_numeric($val)) return $default;
	if ($val<$min) return $min;
	if ($val>$max) return $max;
	return round($val);
}

function interpretMsg($msg) {
	switch ($msg) {
		case "not_yours":
		$e = "You're trying to view someone else's character.";
		errormsg($e);
		break;
		case "no_charid":
		$e = "You're trying to view a character specific page but the character id is missing.";
		errormsg($e);
		break;
		case "no_login":
		$e = "You tried to view a page that requires a login without being logged in.";
		errormsg($e);
		break;
		case "invalid_char":
		$e = "You tried to view a character who doesn't exist.";
		errormsg($e);
		break;
		case "desc_fail":
		$e = "Adding character description failed.";
		errormsg($e);
		break;
		case "ccreate_fail":
		$e = "Character creation failed.";
		errormsg($e);
		break;
		case "ccreate_success":
		$e = "A character was created successfully.";
		infomsg($e);
		break;
		case "cname_success":
		$e = "Character name was changed successfully.";
		infomsg($e);
		break;
		case "cdesc_success":
		$e = "Character description was updated successfully.";
		infomsg($e);
		break;
		case "spawn_success":
		$e = "Your character has successfully entered the world.";
		infomsg($e);
		break;
		case "already_started":
		$e = "You can only select a character's starting location once.";
		errormsg($e);
		break;
		case "missing_data":
		$e = "Required form data is missing.";
		errormsg($e);
		break;
		case "invalid_data":
		$e = "Invalid form data.";
		errormsg($e);
		break;
		case "invalid_spawning_location":
		$e = "Invalid starting location.";
		errormsg($e);
		break;
		default:
		$e = "Unknown message code.";
		errormsg($e);
	}
}

function isUsername($element)
{
	return !preg_match ("/[^A-z0-9_\-]/", $element);
}

function checkFreeUsername($mysqli, $teststring) {
	$utable = new userTable($mysqli);
	$data = $utable->getData("`username` LIKE '$teststring'", 1, NULL, true);//count only
	if ($data) {
		$e = new CustomError("in_use");
		return $e;
	}
	$check = checkPending($mysqli, $teststring);
	if ($check) {
		$e = new CustomError("in_pending");
		return $e;
	}
	
	return 0;
}

function checkPending($mysqli, $teststring) {
	$putable = new pendingUsersTable($mysqli);
	$data = $putable->getData("`username` LIKE '$teststring'", 1, NULL, true);//count only
	if ($data) {
		return true;
	}
	return false;
}

function getExistingAccount($mysqli, $username) {
	$utable = new userTable($mysqli);
	$data = $utable->getData("`username` LIKE '$username'", 1);
	if ($data) return $data[0];//type: assoc
	$e = new CustomError("no_match_account");
	return $e;
}

function generateActivationCode($mysqli, $username, $email, $passhash, $type=1, $userid='NULL') {
	$activation = getRandomPhrase();
	$activation = $mysqli->real_escape_string($activation);
	
	if ($type==3) {//password reset
		$info = getExistingAccount($mysqli, $username);
		if (is_a($info, "CustomError")) return $info;
		if ($info['email']!=$email) {
			$e = new CustomError("wrong_email");
			return $e;
		}	
	}
	if ($type==2) {//email change
		$info = getExistingAccount($mysqli, $username);
		if (is_a($info, "CustomError")) return $info;
		if ($info['passhash2']!=$passhash) {
			$e = new CustomError("wrong_password");
			return $e;
		}	
	}
	
	$putable = new pendingUsersTable($mysqli);
	$now = date('Y-m-d H:i:s');
	$data = array(
		'username' => $username,
		'passhash' => $passhash,
		'email' => $email,
		'joined' => $now,
		'activation' => $activation,
		'type' => $type,
		'userid' => $userid
		);
	$result = $putable->insertRecord($data);
	
	if (!$result) {
		$e = new CustomError("db_error");//Technically this should never be triggered because this is only triggered on duplicate id and the column is auto-increment, so only if it runs out of numbers then it might theoretically trigger this
		return $e;
	}
	
	$mailcheck = mailActivation($mysqli, $email, $type);
	if (!$mailcheck) {
		$e = new CustomError("mail_fail");
		return $e;
	}	
	return 1;
}

function mailActivation($mysqli, $email, $type) {
	if ($type>3||$type<1) return false;//just a precaution
	//March 9, 2018: Combined all 3 types of activation to use the same mailer function
	$info = array();
	$info[] = array(
		'title' => "Welcome to Otherworld!",
		'explanation' => "Your email address was used to request an account from Otherworld-PBBG.com. Below are your activation details.\n\n",
		'instructions' => "Go to http://www.otherworld-pbbg.com/activate.php and paste your activation code in the box.\n\n",
		'disclaimer' => "Forgotten passwords cannot be recovered, but you can reguest for a password reset if you forget your password. Password reset requires access to this email address you registered with. Keep your email address up to date because if you forget your password and don't have a valid email address, you lose access to your account permanently.\n",
	);
	$info[] = array(
		'title' => "Otherworld email change request",
		'explanation' => "An account in Otherworld-PBBG.com set this as their new email address. Details are below.\n\n",
		'instructions' => "Go to http://www.otherworld-pbbg.com/activate.php and paste your activation code in the box.\n\n",
		'disclaimer' => "If you request for a password reset before confirming your email change, the activation code will go in the old email address, so if you no longer have access to that or it's blank, make sure to use this activation code first before reseting your password.\n\n",
	);
	$info[] = array(
		'title' => "Otherworld password reset request",
		'explanation' => "An account in Otherworld-PBBG.com linked to this email address requested for a password reset. Details are below.\n\n",
		'instructions' => "Go to http://www.otherworld-pbbg.com/activate.php , paste your activation code in the box and provide a new password.\n\n",
		'disclaimer' => "Until you enter the correct code, the account will continue being accessible with the old password.\n\n"
	);
	$putable = new pendingUsersTable($mysqli);
	$data = $putable->getData("`email` LIKE '$email' AND `type`=$type", 1, '`uid` DESC');
	if ($data) {
		$assoc = $data[0];
		
		$msg = $info[$type-1]['explanation'];
		$msg .= "Username: " . $assoc['username'] . "\n";
		$msg .= "Activation code: " . $assoc['activation'] . "\n\n";
		$msg .= $info[$type-1]['instructions'];
		$msg .= $info[$type-1]['disclaimer'];
		$msg .= "The request was made on " .  $assoc['joined'] . " server time. If you did not request this message, you can just ignore it.\n\n";
		$msg .= "(This is an automatically sent message. Don't reply to it because the reply address doesn't actually exist.)";
		
		$msg = wordwrap($msg,70);
		$headers = "From: noreply@otherworld-pbbg.com";
		
		$result = mail($email, $info[$type-1]['title'], $msg, $headers);
		
		if ($result) return 1;
	}
	return false;
}

function activateAccount($mysqli, $username, $activation) {
	$putable = new pendingUsersTable($mysqli);
	$data = $putable->getData("`username` LIKE '$username' AND `activation` LIKE '$activation' AND `type`=1", 1, '`uid` DESC');
	if ($data) {
		$assoc = $data[0];
		$newUser = new User($mysqli, array(
				'username' => $assoc['username'],
				'passhash2' => $assoc['passhash'],
				'email' => $assoc['email']
			));
		$result = $newUser->getId();//returns 0 if user creation failed
		if ($result) {
			$info = array('uid' => $assoc['uid']);
			$r = $putable->deleteRecord($info);
			if (!$r) {
				$e = new CustomError("left_pending_a");
				return $e;
			}//user account was generated successfully but pending account was left hanging
			return 100;//success
		}
		$e = new CustomError("ucreate_fail");
		return $e;//creating user account failed
	}
	
	$e = new CustomError("wrong_activation");
	return $e;//activation code is wrong or username doesn't exist
}

function activateEmail($mysqli, $username, $activation) {
	$putable = new pendingUsersTable($mysqli);
	$data = $putable->getData("`username` LIKE '$username' AND `activation` LIKE '$activation' AND `type`=2", 1, '`uid` DESC');
	if ($data) {
		$pu_entry = $data[0];
		$utable = new userTable($mysqli);
		$data2 = $utable->getData("`uid`=" . $pu_entry['userid'], 1);
		$user_entry = $data2[0];
		$info = array(
			'uid' => $user_entry['uid'],
			'email' => $pu_entry['email']
		);
		$success = $utable->updateRecord($info);
		if ($success) {
			$info = array('uid' => $pu_entry['uid']);
			$r = $putable->deleteRecord($info);
			if (!$r) {
				$e = new CustomError("left_pending_a");
				return $e;
			}//email was changed successfully but pending account was left hanging
			return 100;//success
		}
		$e = new CustomError("echange_fail");
		return $e;//changing email failed
	}
	$e = new CustomError("wrong_activation");
	return $e;//activation code is wrong or username doesn't exist
}

function resetPassword($mysqli, $username, $activation, $passhash) {
	$putable = new pendingUsersTable($mysqli);
	$data = $putable->getData("`username` LIKE '$username' AND `activation` LIKE '$activation' AND `type`=3", 1, '`uid` DESC');
	if ($data) {
		$pu_entry = $data[0];
		$utable = new userTable($mysqli);
		$data2 = $utable->getData("`uid`=" . $pu_entry['userid'], 1);
		$user_entry = $data2[0];
		$info = array(
			'uid' => $user_entry['uid'],
			'passhash2' => $passhash
		);
		$success = $utable->updateRecord($info);
		if ($success) {
			$info = array('uid' => $pu_entry['uid']);
			$r = $putable->deleteRecord($info);
			if (!$r) {
				$e = new CustomError("left_pending_p");
				return $e;
			}//password was changed successfully but pending account was left hanging
			return 100;//success
		}
		$e = new CustomError("pchange_fail");
		return $e;//changing password failed
	}
	$e = new CustomError("wrong_activation");
	return $e;//activation code is wrong or username doesn't exist
}

function backlink($pname, $url) {
	starttag('p', '', array('class' => 'right'));
	ptag('a', "[Return to $pname]", array('href' => "$url"));
	closetag('p');
}

function getActivityLog($mysqli, $limit=100) {
	$atable = new activityTable($mysqli);
	$data = $atable->getData("1", $limit, '`uid` DESC');
	if ($data) return $data;
	return false;
}
?>
