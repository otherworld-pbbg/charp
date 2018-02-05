<?php

class Chat {
	private $mysqli;
	private $id;
	private $location;
	private $author;
	private $name;
	private $summary;

	public function __construct($mysqli, $id=0, $location=0, $author=0, $name='', $summary='') {
		$this->mysqli = $mysqli;
		$this->id = $id;
		$this->location = $location;
		$this->author = $author;
		$this->name = $name;
		$this->summary = $summary;
		
		if ($id>0&&$location==0&&$author==0&&$name=='') $this->fetchFromDB();
		else if ($id == 0) $this->addNew($location, $author, $name, $summary);
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
		else $this->id = 0;
	}
	
	public function addNew($location, $author, $name, $summary) {
		$sql = "INSERT INTO `chats` (`uid`, `location`, `author`, `name`, `summary`)"
		. "VALUES (NULL, $location, $author, '$name', '$summary')";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) {
			$this->id = $this->mysqli->insert_id;
			$this->location = $location;
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
	
	public function getSummary() {
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
	
	public function addParticipant($charid) {
		$sql = "INSERT INTO `chat_participants` (`uid`, `chat`, `charid`, `joined`, `leaving`) VALUES (NULL, " . $this->id . ", $charid, CURRENT_TIMESTAMP(), '0000-00-00 00:00:00')";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) return $this->mysqli->insert_id;
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
	
	public function countUnseen($lastseenID=0) {
		$sql = "SELECT count(`uid`) as `num` FROM `chat_msg` WHERE `chat`='" . $this->id . "' AND `uid`>$lastseenID ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows==1) {
			$row = $res->fetch_row();
			return $row[0];
		}
		return -1;//This technically shouldn't happen ever, considering the query returns 0 if there are no hits
	}
	
	public function addMessage($actorid, $contents) {
		$sql = "INSERT INTO `chat_msg` (`uid`, `chat`, `actor`, `timestamp`, `contents`) VALUES (NULL, ". $this->id .", $actorid, CURRENT_TIMESTAMP(), '$contents')";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows==1) return $this->mysqli->insert_id;
		return false;
	}
	
	public function printJoinLink($joinerid) {
		ptag('a', 'Join ' . $this->getName(), array(
			'href' => 'index.php?page=joinChat&charid=' . $joinerid . '&chat=' . $this->id
			));
	}
	
	public function printLeaveLink($leaverid) {

		ptag('a', 'Leave ' . $this->getName(), array(
			'href' => 'index.php?page=leaveChat&charid=' . $leaverid . '&chat=' . $this->id
			));
	}
}
?>
