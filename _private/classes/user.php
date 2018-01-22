<?php
require_once('character.php');

class User {
	private $mysqli;
	private $id;
	private $username;//usernames are permanent and cannot be changed after registration
	private $passhash;
	private $email;
	private $joined;
	
	public function __construct($mysqli, $id=0, $username='', $passhash='', $email='') {
		$this->mysqli = $mysqli;
		$this->id = $id;
		
		if ($id>0) $this->fetchFromDB();
		else if ($id == 0 && $username && $passhash && $email) $this->addNew($username, $passhash, $email);
	}
	
	private function fetchFromDB() {
		$sql = "SELECT `username`, `passhash2`, `email`, `joined` FROM `users` WHERE `uid`=$this->id LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$arr = $res->fetch_assoc();
			$this->username = $arr["username"];
			$this->passhash = $arr["passhash2"];
			$this->email = $arr["email"];
			$this->joined = $arr["joined"];
		}
	}
	
	public function addNew($username, $passhash, $email) {
		$sql = "INSERT INTO `users` (`uid`, `username`, `passhash2`, `email`, `joined`)"
		. "VALUES (NULL, '$username', '$passhash', '$email', CURRENT_TIMESTAMP() )";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->id = $this->mysqli->insert_id;
			$this->username = $username;
			$this->passhash = $passhash;
			return $this->id;
		}
		return false;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	function logLogin() {
		$sql = "INSERT INTO `activity_log` (`userFK`, `timestamp`) VALUES ($this->id, CURRENT_TIMESTAMP)";
		$this->mysqli->query($sql);
	}
	
	function countLogins() {
		$sql = "SELECT COUNT(`uid`) as `logs` FROM `activity_log` WHERE `userFK`=$this->id";
		$res = $this->mysqli->query($sql);
		if ($res) {
			$arr = $res->fetch_assoc();
			return $arr['logs'];
		}
		return -1;
	}
	
	public function addCharacter($name='', $sex=3, $location=0) {
		//to do: Check that player isn't prevented from creating characters
		$newChar = new Character($this->mysqli, 0, $sex, $name, $this->id, $location);
		return $newChar;
	}
	
	public function getCharacters() {
		//to do: separate living and dead
		$sql = "SELECT `uid`, `name` FROM `characters` WHERE `owner`=$this->id ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$charArr = array();
			$counter = 0;
			while ($arr = $res->fetch_assoc()) {
				$charArr[$counter] = new Character($this->mysqli, $arr["uid"], $arr["name"], $this->id);
				$counter++;
			}
			return $charArr;
		}
		return false;
	}
	
	public function changePasshash($new) {
		$sql = "UPDATE `users` SET `passhash2`='$new' WHERE `uid`=$this->id LIMIT 1";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->passhash = $new;
			return true;
		}
		return false;
	}
	
	public function changeEmail($new) {
		//to do - validate new email before this is called
		$sql = "UPDATE `users` SET `email`='$new' WHERE `uid`=$this->id LIMIT 1";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->email = $new;
			return true;
		}
		return false;
	}
	
	public function getMemo($targetid) {
		$sql = "SELECT `uid`, `contents` FROM `memos` WHERE `userid`=" . $this->id . " AND `subject`=$targetid LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows==1) {
			return $res->fetch_assoc();
		}
		return false;
	}
	
	public function setMemo($targetid, $contents) {
		$old = $this->getMemo($targetid);
		if (!$old) {
			$sql = "INSERT INTO `memos` (`uid`, `userid`, `subject`, `contents`) VALUES (NULL, $this->id, $targetid, '$contents')";
			$this->mysqli->query($sql);
			if ($this->mysqli->affected_rows==1) return $this->mysqli->insert_id;
		}
		else if ($old["contents"] == $contents) {
			return false;//trying to save without changes
		}
		else {
			$sql = "UPDATE `memos` SET `contents`='$contents' WHERE `uid`=" . $old["uid"] . " LIMIT 1";
			$this->mysqli->query($sql);
			if ($this->mysqli->affected_rows==1) return $old["uid"];
			else return false;
		}
	}
}
?>
