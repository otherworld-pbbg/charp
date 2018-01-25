<?php
require_once("tags_printer.php");//errormsg, infomsg function
require_once("dictionary.php");
require_once("classes/custom_error.php");
require_once("classes/user.php");

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

function queryDelete($mysqli, $table, $where, $order, $limit=0) {
	if ($limit==0) $sql = "DELETE FROM `$table` WHERE $where ORDER BY $order";
	else $sql = "DELETE FROM `$table` WHERE $where ORDER BY $order LIMIT $limit";
	$mysqli->query($sql);
	return $mysqli->affected_rows;
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
	$sql = "SELECT `username` FROM `users` WHERE `username` LIKE '$teststring' LIMIT 1";
	$res = $mysqli->query($sql);
	if (mysqli_num_rows($res)) {
		$e = new CustomError("in_use");
		return $e;
	}
	
	$sql = "SELECT `username` FROM `pending_users` WHERE `username` LIKE '$teststring' LIMIT 1";
	$res = $mysqli->query($sql);
	if (mysqli_num_rows($res)) {
		$e = new CustomError("in_pending");
		return $e;
	}
	
	return 0;
}

function checkPending($mysqli, $teststring) {
	$sql = "SELECT `username` FROM `pending_users` WHERE `username` LIKE '$teststring' LIMIT 1";
	$res = $mysqli->query($sql);
	if (mysqli_num_rows($res)) {
		return true;
	}
	return false;
}

function getExistingAccount($mysqli, $username) {
	$res = $mysqli->query("SELECT `uid`, `passhash`, `passhash2`, `email`, `joined` FROM users WHERE `username` like '$username' LIMIT 1");
	if (!$res) {
		$e = new CustomError("db_error");
		return $e;
	}		
	if ($res->num_rows == 0) {
		$e = new CustomError("no_match_account");
		return $e;
	}
	else {
		return $res->fetch_object();
	}
}

function generateActivationCode($mysqli, $username, $email, $passhash, $type=1, $userid='NULL') {
	$activation = getRandomPhrase();
	$activation = $mysqli->real_escape_string($activation);
	
	if ($type==3) {//password reset
		$info = getExistingAccount($mysqli, $username);
		if (is_a($info, "CustomError")) return $info;
		if ($info->email!=$email) {
			$e = new CustomError("wrong_email");
			return $e;
		}	
	}
	if ($type==2) {//email change
		$info = getExistingAccount($mysqli, $username);
		if (is_a($info, "CustomError")) return $info;
		if ($info->passhash2!=$passhash) {
			$e = new CustomError("wrong_password");
			return $e;
		}	
	}
	
	$sql = "INSERT INTO `pending_users` (`username`, `passhash`, `email`, `joined`, `activation`, `type`, `userid`) VALUES ('$username', '$passhash', '$email', CURRENT_TIMESTAMP(), '$activation', '$type', $userid)";
	$mysqli->query($sql);
	$result = $mysqli->insert_id;
	if (!$result) return 0;
	
	if ($type==1) $mailcheck = mailActivation($mysqli, $email);
	if ($type==2) $mailcheck = mailEmailChange($mysqli, $email);
	if ($type==3) $mailcheck = mailPasswordReset($mysqli, $email);
	if (!$mailcheck) {
		$e = new CustomError("mail_fail");
		return $e;
	}	
	return 1;
}

function mailActivation($mysqli, $email) {
	$sql = "SELECT `username`, `activation`, `joined` FROM `pending_users` WHERE `email` LIKE '$email' AND `type`=1 LIMIT 1";
	$res = $mysqli->query($sql);
	if ($res->num_rows) {
		$row = mysqli_fetch_object($res);
		
		$msg = "Your email address was used to request an account from Otherworld-PBBG.com. Below are your activation details.\n\n";
		$msg .= "Username: " . $row->username . "\n";
		$msg .= "Password: (what ever you registered with, withheld for security purposes)\n";
		$msg .= "Activation code: " . $row->activation . "\n\n";
		$msg .= "Go to http://www.otherworld-pbbg.com/activate.php and paste your activation code in the box.\n\n";
		$msg .= "Disclaimer: Forgotten passwords cannot be recovered, but you can reguest for a password reset if you forget your password. Password reset requires access to this email address you registered with. Keep your email address up to date because if you forget your password and don't have a valid email address, you lose access to your account permanently.\n";
		$msg .= "The request was made on $row->joined server time. If you did not request this message, you can just ignore it and the pending account will be purged in 24 hours.\n\n";
		$msg .= "(This is an automatically sent message. Don't reply to it because the reply address doesn't actually exist.)";
		
		$msg = wordwrap($msg,70);
		$headers = "From: noreply@otherworld-pbbg.com";
		
		$result = mail($email, "Welcome to Otherworld!", $msg, $headers);
		
		if ($result) return true;
	}
	return false;
}

function mailEmailChange($mysqli, $email) {
	$sql = "SELECT `username`, `activation`, `joined` FROM `pending_users` WHERE `email` LIKE '$email' AND `type`=2 LIMIT 1";
	$res = $mysqli->query($sql);
	if ($res->num_rows) {
		$row = mysqli_fetch_object($res);
		
		$msg = "An account in Otherworld-PBBG.com set this as their new email address. Details are below.\n\n";
		$msg .= "Username: " . $row->username . "\n";
		$msg .= "Activation code: " . $row->activation . "\n\n";
		$msg .= "Go to http://www.otherworld-pbbg.com/activate.php and paste your activation code in the box.\n\n";
		$msg .= "If you request for a password reset before confirming your email change, the activation code will go in the old email address, so if you no longer have access to that or it's blank, make sure to use this activation code first before reseting your password.\n\n";
		$msg .= "The request was made on $row->joined server time. If you did not request this message, you can just ignore it and the email change request will be purged in 24 hours.\n\n";
		$msg .= "(This is an automatically sent message. Don't reply to it because the reply address doesn't actually exist.)";
		
		$msg = wordwrap($msg,70);
		$headers = "From: noreply@otherworld-pbbg.com";
		
		$result = mail($email, "Otherworld email change request", $msg, $headers);
		
		if ($result) return true;
	}
	return false;
}

function mailPasswordReset($mysqli, $email) {
	$sql = "SELECT `email`, `username`, `activation`, `joined` FROM `pending_users` WHERE `email` LIKE '$email' AND `type`=3 LIMIT 1";
	$res = $mysqli->query($sql);
	if ($res->num_rows) {
		$row = mysqli_fetch_object($res);
		
		$msg = "An account in Otherworld-PBBG.com linked to this email address requested for a password reset. Details are below.\n\n";
		$msg .= "Username: " . $row->username . "\n";
		$msg .= "Activation code: " . $row->activation . "\n\n";
		$msg .= "Go to http://www.otherworld-pbbg.com/activate.php , paste your activation code in the box and provide a new password.\n\n";
		$msg .= "Until you enter the correct code, the account will continue being accessible with the old password.\n\n";
		$msg .= "The request was made on $row->joined server time. If you did not request this message, or requested it accidentally, you can just ignore it and the password reset request will be purged in 24 hours.\n\n";
		$msg .= "(This is an automatically sent message. Don't reply to it because the reply address doesn't actually exist.)";
		
		$msg = wordwrap($msg,70);
		$headers = "From: noreply@otherworld-pbbg.com";
		
		$result = mail($row->email, "Otherworld password reset request", $msg, $headers);
		
		if ($result) return true;
	}
	return false;
}

function activateAccount($mysqli, $username, $activation) {
	$sql = "SELECT `uid`, `username`, `passhash`, `email` FROM `pending_users` WHERE `username` LIKE '$username' AND `activation` LIKE '$activation' AND `type`=1 LIMIT 1";
	$res = $mysqli->query($sql);
	if ($res->num_rows) {
		$row = mysqli_fetch_object($res);
		$newUser = new User($mysqli, 0, $row->username, $row->passhash, $row->email);//now this can call user creation from the constructor
		$result = $newUser->getId();
		if ($result) {
			$r=queryDelete($mysqli, "pending_users", "`uid`=$row->uid", "`uid`", 1);
			if ($r==0) {
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
	$sql = "SELECT `uid`, `email`, `userid` FROM `pending_users` WHERE `username` LIKE '$username' AND `activation` LIKE '$activation' AND `type`=2 LIMIT 1";
	$res = $mysqli->query($sql);
	if ($res->num_rows) {
		$row = mysqli_fetch_object($res);
		$sql2 = "UPDATE `users` SET `email`='$row->email' WHERE `uid`=$row->userid LIMIT 1";
		$mysqli->query($sql2);
		if ($mysqli->affected_rows==1) {
			$r=queryDelete($mysqli, "pending_users", "`uid`=$row->uid", "`uid`", 1);
			if ($r==0) {
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
	$sql = "SELECT `uid`, `userid` FROM `pending_users` WHERE `username` LIKE '$username' AND `activation` LIKE '$activation' AND `type`=3 LIMIT 1";
	$res = $mysqli->query($sql);
	if ($res->num_rows) {
		$row = mysqli_fetch_object($res);
		$sql2 = "UPDATE `users` SET `passhash2`='$passhash' WHERE `uid`=$row->userid LIMIT 1";
		$mysqli->query($sql2);
		if ($mysqli->affected_rows==1) {
			$r=queryDelete($mysqli, "pending_users", "`uid`=$row->uid", "`uid`", 1);
			if ($r==0) {
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
?>