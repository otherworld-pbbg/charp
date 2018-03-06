<?php
require_once("custom_error.php");
include_once('character_table.php');
include_once('description_table.php');
include_once('participant_table.php');
include_once('seen_msg_table.php');

class Character {
	private $mysqli;
	private $uid;
	private $sex;
	private $name;
	private $owner;
	private $location;
	private $table;
	
	public function __construct($mysqli, $data) {
		$this->mysqli = $mysqli;
		$this->uid = isset($data['uid']) ? $data['uid'] : 0;
		$this->sex = isset($data['sex']) ? $data['sex'] : 3;
		$this->name = isset($data['name']) ? $data['name'] : '';
		$this->owner = isset($data['owner']) ? $data['owner'] : 0;
		$this->location = isset($data['location']) ? $data['location'] : 0;
		$this->table = new characterTable($this->mysqli);
		
		if (isset($data['uid'])&&!isset($data['owner'])) {
			//If uid is set and owner is set, it's assumed all necessary
			//data was already passed
			$this->fetchFromDB();
		}
		else if ($this->uid == 0) $this->addNew();
		//You don't need to pass $data because the values were already added to class variables in the constructor
	}
	
	private function fetchFromDB() {
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
			'name' => $this->name,
			'owner' => $this->owner,
			'sex' => $this->sex,
			'location' => $this->location
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
		//only pass sanitized text
		$this->table->updateRecord(array(
			'uid' => $this->uid,
			'name' => $newName
			));
		
		$error = $this->table->getErrors();
		if (!empty($error)) return false;
		
		$this->name = $newName;
		return true;
	}
	
	public function changeLocation($newLoc) {
		$this->table->updateRecord(array(
			'uid' => $this->uid,
			'location' => $newLoc
			));
		
		$error = $this->table->getErrors();
		if (!empty($error)) return $error[0];
		
		$this->location = $newLocation;
		return true;
	}
	
	public function spawn($newLoc) {
		//To do: give starting equipment/wealth
		$check = $this->changeLocation($newLoc);
		if (is_a($check, 'CustomError')) {
			$e = new CustomError('spawn_fail');
			return $e;
		}
		return true;
	}
	
	public function getDescription() {
		$dtable = new descriptionTable($this->mysqli);
		$data = $dtable->getData("`charid`=$this->uid", 1);
		if ($data) return $data[0];//returns assoc
		return false;
	}
	
	public function setDescription($newDescription) {
		$old = $this->getDescription();
		$dtable = new descriptionTable($this->mysqli);
		if (!$old) {
			$data = array(
				'charid' => $this->uid,
				'contents' => $newDescription
			);
			$result = $dtable->insertRecord($data);
			if ($result) {
				return $result;
			}
			return false;
		}
		else if ($old['contents'] == $newDescription) {
			return false;//trying to save without changes
		}
		else {
			$dtable->updateRecord(array(
			'uid' => $old['uid'],
			'contents' => $newDescription
			));
		}
		$error = $dtable->getErrors();
		if (!empty($error)) return $error[0];
		
		return $old["uid"];
	}
	
	public function say($chatO, $contents) {
		//to do: Check that person isn't muted
		return $chatO->addMessage($this->uid, $contents);
	}
	
	public function join($chatO) {
		//to do: check that person isn't barred from joining
		return $chatO->addParticipant($this->uid);
	}
	
	public function leave($chatid) {
		$ptable = new participantTable($this->mysqli);
		$data = $ptable->getData("`chat`=$chatid AND `charid`=$this->uid AND `leaving`='0000-00-00 00:00:00'", 1, '`uid` DESC');
		if ($data) {
			$assoc = $data[0];//It's possible that somebody is listed multiple times but for simplicity we assume there is only one entry
		}
		else return false;
		
		$now = date('Y-m-d H:i:s');
		
		$ptable->updateRecord(array(
			'uid' => $assoc['uid'],
			'leaving' => $now
		));
		
		$error = $ptable->getErrors();
		if (!empty($error)) return false;
		
		return true;
	}
	
	public function getCurrentChat() {
		$ptable = new participantTable($this->mysqli);
		$data = $ptable->getData("`charid`=$this->uid AND `leaving`='0000-00-00 00:00:00'", 1, '`uid` DESC');
		if ($data) {
			$assoc = $data[0];//It's possible that somebody is listed multiple times but for simplicity we assume there is only one entry
			return $assoc['chat'];
		}
		return false;
	}
	
	public function getLastSeen($chat) {
		$smtable = new seenMsgTable($this->mysqli);
		$data = $smtable->getData("`viewer`=$this->uid AND `chat`=$chat", 1, '`uid` DESC');
		if ($data) {
			return $data[0];//This used to just return the number of message but now it
			//gets everything because we need the uid
		}
		return 0;
	}
	
	public function setLastSeen($chat, $msg) {
		$assoc = $this->getLastSeen($chat);
		$smtable = new seenMsgTable($this->mysqli);
		if ($assoc == 0) {
			//doesn't exist, insert
			$data = array(
				'viewer' => $this->uid,
				'chat' => $chat,
				'msg' => $msg
			);
			$result = $smtable->insertRecord($data);
			if ($result) {
				return true;
			}
			return false;
		}
		else if ($assoc['msg'] == $msg) return true;//it already exists, no need to add
		else {
			$smtable->updateRecord(array(
				'uid' => $assoc['uid'],
				'msg' => $msg
			));
			
			$error = $smtable->getErrors();
			if (!empty($error)) return false;
			return true;
		}
	}
}
?>
