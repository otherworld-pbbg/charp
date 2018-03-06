<?php
require_once('character.php');
require_once('character_table.php');
require_once('user_table.php');
require_once('user_rights_table.php');
require_once('activity_table.php');
require_once('memo_table.php');

class User {
	private $mysqli;
	private $id;
	private $username;//usernames are permanent and cannot be changed after registration
	private $passhash;//legacy, will get merged with $passhash2
	private $passhash2;
	private $email;
	private $joined;
	private $table;
	
	public function __construct($mysqli, $data) {
		$this->mysqli = $mysqli;
		$this->uid = isset($data['uid']) ? $data['uid'] : 0;
		$this->username = isset($data['username']) ? $data['username'] : '';
		$this->passhash = isset($data['passhash']) ? $data['passhash'] : '';
		$this->passhash2 = isset($data['passhash2']) ? $data['passhash2'] : '';
		$this->email = isset($data['email']) ? $data['email'] : '';
		$this->table = new userTable($this->mysqli);
		
		if ($this->uid>0) {
			$this->fetchFromDB();
		}
		else if ($this->uid == 0 && isset($data['username']) && isset($data['passhash2']) && isset($data['email'])) $this->addNew();
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
		$now = date('Y-m-d H:i:s');
		$data = array(
			'username' => $this->username,
			'passhash' => $this->passhash,
			'passhash2' => $this->passhash2,
			'email' => $this->email,
			'joined' => $now
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
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	function logLogin() {
		$atable = new activityTable($this->mysqli);
		$now = date('Y-m-d H:i:s');
		$data = array(
			'userFK' => $this->uid,
			'timestamp' => $now
			);
		$result = $atable->insertRecord($data);//This doesn't return anything because it's not critical
	}
	
	public function countLogins() {
		$atable = new activityTable($this->mysqli);
		$count = $atable->getData("`userFK`=$this->uid", NULL, NULL, true);//countOnly=true
		
		if ($count) {
			return $count;
		}
		return 0;
	}
	
	public function addCharacter($name='', $sex=3, $location=0) {
		//to do: Check that player isn't prevented from creating characters
		$newChar = new Character(
			$this->mysqli, array(
				'sex' => $sex,
				'name' => $name,
				'owner' => $this->uid,
				'location' => $location
				)
			);
		return $newChar;
	}
	
	public function getCharacters() {
		$ctable = new characterTable($this->mysqli);
		$data = $ctable->getData("`owner`=$this->uid", NULL, '`uid`');
		if ($data) {
			$retArr = array();
			foreach ($data as $key => $assoc) {
				$temp = new Character(
					$this->mysqli, $assoc);
				$retArr[] = $temp;
			}
			return $retArr;
		}
		return false;
	}
	
	public function changePasshash($new) {
		if ($this->passhash2 == $new) return false;
		$this->table->updateRecord(array(
			'uid' => $this->uid,
			'passhash2' => $new
			));
		
		$error = $this->table->getErrors();
		if (!empty($error)) return false;
		
		$this->passhash2 = $new;
		return true;
	}
	
	public function changeEmail($new) {
		if ($this->email == $new) return false;
		$this->table->updateRecord(array(
			'uid' => $this->uid,
			'email' => $new
			));
		
		$error = $this->table->getErrors();
		if (!empty($error)) return false;
		
		$this->email = $new;
		return true;
	}
	
	public function getMemo($targetid) {
		$mtable = new memoTable($this->mysqli);
		$data = $mtable->getData("`userid`=$this->uid AND `subject`=$targetid", 1);
		
		if ($data) {
			return $data[0];
		}
		return false;
	}
	
	public function setMemo($targetid, $contents) {
		$old = $this->getMemo();
		$mtable = new memoTable($this->mysqli);
		if (!$old) {
			$data = array(
				'userid' => $this->uid,
				'subject' => $targetid,
				'contents' => $contents
			);
			$result = $mtable->insertRecord($data);
			if ($result) {
				return $result;
			}
			return false;
		}
		else if ($old['contents'] == $contents) {
			return false;//trying to save without changes
		}
		else {
			$mtable->updateRecord(array(
			'uid' => $old['uid'],
			'contents' => $contents
			));
		}
		$error = $mtable->getErrors();
		if (!empty($error)) return false;
		
		return $old['uid'];
	}
	
	public function getRight($right) {
		$utable = new userRightsTable($this->mysqli);
		$data = $utable->getData("`userid`=$this->uid AND `permission`=$right", 1);
		
		if ($data) {
			return $data[0];
		}
		return false;
	}
	
	public function setRight($right, $newValue) {
		$old = $this->getRight($right);
		$utable = new userRightsTable($this->mysqli);
		if (!$old) {
			$data = array(
				'userid' => $this->uid,
				'permission' => $right,
				'value' => $newValue
			);
			$result = $utable->insertRecord($data);
			if ($result) {
				return $result;
			}
			return false;
		}
		else if ($old['value'] == $newValue) {
			return false;//trying to save without changes
		}
		else {
			$utable->updateRecord(array(
			'uid' => $old['uid'],
			'value' => $newValue
			));
		}
		$error = $utable->getErrors();
		if (!empty($error)) return false;
		
		return $old['uid'];
	}
	
	public function checkRight($right, $compare, $method='equals') {
		$old = $this->getRight($right);
		if (!$old) return false;
		if ($method == 'equals') return $old['value'] == $compare;
		if ($method == 'lessthan') return $old['value'] < $compare;
		if ($method == 'morethan') return $old['value'] > $compare;
		return false;//Unknown method
	}
	
	public function grantRight($receiver, $right, $newValue) {
		return $receiver->setRight($right, $newValue);//assuming receiver is a valid User
	}
}
?>
