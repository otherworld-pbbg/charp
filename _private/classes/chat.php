<?php
include_once('character_table.php');
include_once('chat_table.php');
include_once('chat_msg_table.php');

class Chat {
	private $mysqli;
	private $uid;
	private $location;
	private $author;
	private $name;
	private $summary;
	private $table;

	public function __construct($mysqli, $data) {
		$this->mysqli = $mysqli;
		$this->uid = isset($data['uid']) ? $data['uid'] : 0;
		$this->location = isset($data['location']) ? $data['location'] : 0;
		$this->author = isset($data['author']) ? $data['author'] : 0;
		$this->name = isset($data['name']) ? $data['name'] : '';
		$this->summary = isset($data['summary']) ? $data['summary'] : '';
		$this->table = new chatTable($this->mysqli);
		
		if (isset($data['uid'])&&!isset($data['author'])) {
			//If uid is set and author is set, it's assumed all necessary
			//data was already passed
			$this->fetchFromDB();
		}
		else if ($this->uid == 0) $this->addNew();
	}
	
	private function fetchFromDB() {//This is just copypasted from Character
		$data = $this->table->getData("`uid`=$this->uid", 1);
		if ($data) {
			$assoc = $data[0];//There will only be one entry because duplicate keys aren't allowed
			foreach ($assoc as $key => $value) {
				$this->{$key} = $value;
			}
			return true;
		}
		$this->uid = 0;//Signals that the entry isn't valid
		return false;
	}
	
	private function addNew() {
		//Now this can only be called from the constructor
		$data = array(
			'location' => $this->location,
			'author' => $this->author,
			'name' => $this->name,
			'summary' => $this->summary
			);
		$result = $this->table->insertRecord($data);
		if ($result) {
			$this->uid = $result;
			return $this->uid;
		}
		return false;//uid remains 0, signaling insert failed
	}
	
	public function getId() {
		return $this->uid;
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
		if ($this->name == $newName && $this->summary == $newSummary) return false;//No change
		//only pass sanitized text
		$this->table->updateRecord(array(
			'uid' => $this->uid,
			'name' => $newName,
			'summary' => $newSummary
			), 1);//limit 1
		
		$error = $this->table->getErrors();
		if (!empty($error)) return false;
		
		$this->name = $newName;
		$this->summary = $newSummary;
		return true;
	}
	
	public function getParticipants($currentOnly = true) {
		$ptable = new participantTable($this->mysqli);
		if ($currentOnly) $data = $ptable->getData("`chat`=$this->uid AND `leaving`='0000-00-00 00:00:00'", NULL, '`uid` DESC');
		else $data = $ptable->getData("`chat`=$this->uid", NULL, '`uid` DESC');
		if ($data) {
			return $data;
		}
		return false;
	}
	
	public function addParticipant($charid) {
		$ptable = new participantTable($this->mysqli);
		$now = date('Y-m-d H:i:s');
		$data = array(
			'chat' => $this->uid,
			'charid' => $charid,
			'joined' => $now,
			'leaving' => '0000-00-00 00:00:00'
			);
		$result = $ptable->insertRecord($data);
		if ($result) {
			return $result;
		}
		return false;
	}
	
	public function getMessages($lastseenID=0) {
		$cmtable = new chatMsgTable($this->mysqli);
		$data = $cmtable->getData("`chat`=$this->uid AND `uid`>$lastseenID", NULL, '`uid` ASC');
		
		if ($data) {
			return $data;
		}
		return false;
	}
	
	public function countUnseen($lastseenID=0) {
		$cmtable = new chatMsgTable($this->mysqli);
		$count = $cmtable->getData("`chat`=$this->uid AND `uid`>$lastseenID", NULL, NULL, true);//countOnly=true
		
		if ($count) {
			return $count;
		}
		return 0;
	}
	
	public function addMessage($actorid, $contents) {
		$cmtable = new chatMsgTable($this->mysqli);
		$now = date('Y-m-d H:i:s');
		$data = array(
			'chat' => $this->uid,
			'actor' => $actorid,
			'timestamp' => $now,
			'contents' => $contents
			);
		$result = $cmtable->insertRecord($data);
		if ($result) {
			return $result;
		}
		return false;
	}
	
	public function printJoinLink($joinerid) {
		ptag('a', 'Join ' . $this->getName(), array(
			'href' => 'index.php?page=joinChat&charid=' . $joinerid . '&chat=' . $this->uid
			));
	}
	
	public function printLeaveLink($leaverid) {

		ptag('a', 'Leave ' . $this->getName(), array(
			'href' => 'index.php?page=leaveChat&charid=' . $leaverid . '&chat=' . $this->uid
			));
	}
}
?>
