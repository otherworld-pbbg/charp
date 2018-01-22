<?php

class CustomDisclaimer {
	private $key;
	
	function __construct($key) {
		$this->key = $key;
	}
	
	public function getMsg() {
		switch ($this->key) {
		case "left_pending_a":
			return "Your account was registered successfully but a ghost was left in the pending registry. Don't worry about that, though, it will be deleted later on.";
		case "left_pending_e":
			return "Your email was changed successfully but a ghost was left in the pending registry. Don't worry about that, though, it will be deleted later on.";
		case "left_pending_p":
			return "Your password was changed successfully but a ghost was left in the pending registry. Don't worry about that, though, it will be deleted later on.";
		default:
			return "Unknown disclaimer code.";
		}
	}
}
?>