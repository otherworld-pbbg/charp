<?php

class Character {
	private $mysqli;
	private $id;
	private $sex;
	private $name;
	private $owner;
	private $location;
	
	public function __construct($mysqli, $id=0, $sex=3, $name='', $owner=0, $location=0) {
		$this->mysqli = $mysqli;
		$this->id = $id;
		$this->sex = $sex;
		$this->name = $name;
		$this->owner = $owner;
		$this->location = $location;
		
		if ($id>0 && $owner == 0) $this->fetchFromDB();//It's possible to initialize this object with all the necessary information, but if only id is given, it will get the rest from the db
		else if ($id == 0 && $owner>0) {
			$this->addNew($name, $owner, $sex, $location);//Now it's possible to add this to the database with the constructor
		}
	}
	
	private function fetchFromDB() {
		$sql = "SELECT `name`, `owner`, `sex`, `location` FROM `characters` WHERE `uid`=$this->id LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$arr = $res->fetch_assoc();
			$this->name = $arr["name"];
			$this->owner = $arr["owner"];
			$this->sex = $arr['sex'];
			$this->location = $arr['location'];
			return true;
		}
		return false;
	}
	
	public function checkIfExists() {
		return $this->fetchFromDB();
	}
	
	public function addNew($name, $owner, $sex=3, $location=0) {
		$sql = "INSERT INTO `characters` (`uid`, `name`, `owner`, `sex`, `location`)"
		. "VALUES (NULL, '$name', $owner, $sex, $location)";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->id = $this->mysqli->insert_id;
			$this->name = $name;
			$this->owner = $owner;
			$this->sex = $sex;
			$this->location = $location;
			return $this->id;
		}
		return false;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		if ($this->name == '') return '(unnamed)';
		return $this->name;
	}
	
	public function getOwner() {
		return $this->owner;
	}
	
	public function getSex() {
		return $this->sex;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function verbalizeSex() {
		if ($this->sex == 1) return "male";
		if ($this->sex == 2) return "female";
		return "ambiguous";
	}
	
	public function changeName($newName) {
		$sql = "UPDATE `characters` SET `name`='$newName' WHERE `uid`=$this->id LIMIT 1";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->name = $newName;
			return true;
		}
		return false;
	}
	
	public function changeLocation($newLoc) {
		$sql = "UPDATE `characters` SET `location`=$newLoc WHERE `uid`=$this->id LIMIT 1";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->location = $newLoc;
			return true;
		}
		return false;
	}
	
	public function getDescription() {
		$sql = "SELECT `uid`, `contents` FROM `descriptions` WHERE `charid`=" . $this->id . " LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows==1) {
			return $res->fetch_assoc();
		}
		return false;
	}
	
	public function setDescription($newDescription) {
		$old = $this->getDescription();
		if (!$old) {
			$sql = "INSERT INTO `descriptions` (`uid`, `charid`, `contents`) VALUES (NULL, $this->id, '$newDescription')";
			$this->mysqli->query($sql);
			if ($this->mysqli->affected_rows==1) return $this->mysqli->insert_id;
		}
		else if ($old["contents"] == $newDescription) {
			return false;//trying to save without changes
		}
		else {
			$sql = "UPDATE `descriptions` SET `contents`='$newDescription' WHERE `uid`=" . $old["uid"] . " LIMIT 1";
			$this->mysqli->query($sql);
			if ($this->mysqli->affected_rows==1) return $old["uid"];
			else return false;
		}
	}
	
	public function say($chatO, $contents) {
		//to do: Check that person isn't muted
		return $chatO->addMessage($this->id, $contents);
	}
	
	public function getCurrentChat() {
		$sql = "SELECT `chat` FROM `chat_participants` WHERE `charid`=" . $this->id . " AND `leaving`='0000-00-00 00:00:00' ORDER BY `uid` DESC LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$info = $res->fetch_assoc();
			return $info['chat'];
		}
		return false;
	}
	
	public function getLastSeen($chat) {
		$sql = "SELECT `msg` FROM `seen_msg` WHERE `viewer`=" . $this->id . " AND `chat`=$chat ORDER BY `uid` DESC LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$info = $res->fetch_assoc();
			return $info['msg'];
		}
		return 0;
	}
	
	public function setLastSeen($chat, $msg) {
		$sql = "UPDATE `seen_msg` SET `msg`=$msg WHERE `viewer`=" . $this->id . " AND `chat`=$chat";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows>0) return true;
		$old = $this->getLastSeen($chat, $msg);
		if ($old == $msg) return true;//it already exists, no need to add
		$sql = "INSERT INTO `seen_msg` (`viewer`, `chat`, `msg`) VALUES (" . $this->id . ", $chat, $msg)";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) return true;
		return false;
	}
}
?>
