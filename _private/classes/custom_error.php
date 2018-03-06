<?php

class CustomError {
	private $key;
	
	function __construct($key) {
		$this->key = $key;
	}
	
	public function getMsg() {
		switch ($this->key) {
		case "db_error":
			return "A database error occurred.";
		case "empty_data":
			return "Empty data set.";
		case "duplicate_id":
			return "An entry already exists with this id.";
		case "no_pkey":
			return "Tried to update without referring to a particular row.";
		case "in_use":
			return "This username is already taken. Pick something else.";
		case "in_pending":
			return "There's already a pending account with this username. If this is yours and you don't have the activation code, have it resent. If it belongs to someone else, you need to pick some other username.";
		case "no_match_account":
			return "There is no account with this information.";
		case "wrong_email":
			return "The given email address doesn't match what's on file.";
		case "wrong_password":
			return "Wrong password!";
		case "mail_fail":
			return "Sending email failed.";
		case "ucreate_fail":
			return "The code was correct but for some reason, an account couldn't be created. Please contact administration.";
		case "ccreate_fail":
			return "Character creation failed.";
		case "echange_fail":
			return "The code was correct but for some reason, your email couldn't be changed. Please contact administration.";
		case "pchange_fail":
			return "Changing password failed.";
		case "wrong_activation":
			return "Wrong activation code (or type)!";
		case "left_pending_a":
			return "Your account was registered successfully but a ghost was left in the pending registry. Don't worry about that, though, it will be deleted later on.";
		case "left_pending_e":
			return "Your email was changed successfully but a ghost was left in the pending registry. Don't worry about that, though, it will be deleted later on.";
		case "left_pending_p":
			return "Your password was changed successfully but a ghost was left in the pending registry. Don't worry about that, though, it will be deleted later on.";
		default:
			return "Unknown error code.";
		}
	}
}
?>