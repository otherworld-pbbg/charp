<?php

class Chat {
	private $mysqli;
	private $id;
	private $location;
	private $author;
	private $name;
	private $summary;

	public function __construct($mysqli, $id=0) {
		$this->mysqli = $mysqli;
		$this->id = $id;
		
		if ($id>0) $this->fetchFromDB();
	}
	
	private function fetchFromDB() {
		$sql = "SELECT `location`, `author`, `name`, `summary` FROM `chats` WHERE `uid`=" . $this->id . " LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$arr = $res->fetch_assoc();
			$this->location = $arr['location'];
			$this->author = $arr["author"];
			$this->name = $arr["name"];
			$this->summary = $arr["summary"];
		}
	}
	
	public function addNew($location, $author, $name, $summary) {
		$sql = "INSERT INTO `chats` (`uid`, `location`, `author`, `name`, `summary`)"
		. "VALUES (NULL, $location, $author, '$name', '$summary')";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->id = $this->mysqli->insert_id;
			$this->author = $author;
			$this->name = $name;
			$this->summary = $summary;
			return $this->id;
		}
		return false;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function getName() {
		if ($this->name == '') return '(unnamed)';
		return $this->name;
	}
	
	public function geSummary() {
		return $this->summary;
	}
	
	public function getAuthor() {
		return $this->author;
	}
	
	public function change($newName, $newSummary) {
		$sql = "UPDATE `chats` SET `name`='$newName', `summary`='$newSummary' WHERE `uid`=$this->id LIMIT 1";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->name = $newName;
			$this->summary = $newSummary;
			return true;
		}
		return false;
	}
	
	public function getParticipants($currentOnly = true) {
		if ($currentOnly) $sql = "SELECT `uid`, `charid`, `joined`, `leaving` FROM `chat_participants` WHERE `chat`=" . $this->id . " AND `leaving`='0000-00-00 00:00:00' ORDER BY `uid`";
		else $sql = "SELECT `uid`, `charid`, `joined`, `leaving` FROM `chat_participants` WHERE `chat`=" . $this->id . " ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$retArr = array();
			while ($arr = $res->fetch_assoc()) {
				$retArr[] = $arr;
			}
			return $retArr;
		}
		return false;
	}
	
	public function getMessages($lastseenID=0) {
		$sql = "SELECT `uid`, `actor`, `timestamp`, `contents` FROM `chat_msg` WHERE `chat`=" . $this->id . " AND `uid`>$lastseenID ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$retArr = array();
			while ($arr = $res->fetch_assoc()) {
				$retArr[] = $arr;
			}
			return $retArr;
		}
		return false;
	}
	
	public function addMessage($actorid, $contents) {
		$sql = "INSERT INTO `chat_msg` (`uid`, `chat`, `actor`, `timestamp`, `contents`) VALUES (NULL, ". $this->id .", $actorid, CURRENT_TIMESTAMP(), '$contents')";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) return $this->mysqli->insert_id;
		return false;
	}
}
?>
