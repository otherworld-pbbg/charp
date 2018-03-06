<?php
include_once('location_table.php');
include_once('chat_table.php');
include_once('chat.php');
//type 1 - continent, 2 - region, 3 - town, village or camp, 4 - district, 5 - house or shop, 0 - traveling or temporary

class Location {
	private $mysqli;
	private $uid;
	private $name;
	private $parent;
	private $type;
	private $description;
	private $spawning;
	private $table;
	
	public function __construct($mysqli, $data) {
		$this->mysqli = $mysqli;
		$this->uid = isset($data['uid']) ? $data['uid'] : 0;
		$this->name = isset($data['name']) ? $data['name'] : '';
		$this->parent = isset($data['parent']) ? $data['parent'] : 0;
		$this->type = isset($data['type']) ? $data['type'] : 0;
		$this->description = isset($data['description']) ? $data['description'] : 0;
		$this->spawning = isset($data['spawning']) ? $data['spawning'] : 0;
		$this->table = new locationTable($this->mysqli);
		
		if (isset($data['uid'])&&$this->type==0) {
			//If uid is set and type is set, it's assumed all necessary
			//data was already passed
			$this->fetchFromDB();
		}
		else if ($this->uid == 0&&$this->type>0) $this->addNew();
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
			'parent' => $this->parent,
			'type' => $this->type,
			'spawning' => $this->spawning,
			'description' => $this->description
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
		if ($this->uid==0) return 'The Limbo';
		if ($this->name == '') return '(unnamed)';
		return $this->name;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getSpawning() {
		return $this->spawning;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setSpawning($new) {
		if ($this->spawning == $new) return false;//Already the same
		$this->table->updateRecord(array(
			'uid' => $this->uid,
			'spawning' => $new
			));
		
		$error = $this->table->getErrors();
		if (!empty($error)) return false;
		
		$this->spawning = $new;
		return true;
	}
	
	public function getChildLocations() {
		$data = $this->table->getData("`parent`=$this->uid", NULL, '`uid` ASC');
		if ($data) {
			$retArr = array();
			foreach ($data as $assoc) {
				$temp = new Location($this->mysqli, $assoc);
				$retArr[] = $temp;
			}
			return $retArr;
		}
		return false;
	}
	
	public function printChildren() {
		$children = $this->getChildLocations();
		if ($children) {
			foreach($children as $child) {
				starttag('ul');
				starttag('li');
				ptag('h' . $child->getType(), $child->getName());
				para($child->getDescription());
				closetag('li');
				$child->printChildren();
				closetag('ul');
			}
		}
	}
	
	public function getChats() {
		$ctable = new chatTable($this->mysqli);
		$data = $ctable->getData("`location`=$this->uid", NULL, '`uid` ASC');
		if ($data) {
			$retArr = array();
			foreach ($data as $assoc) {
				$temp = new Chat($this->mysqli, $assoc);
				$retArr[] = $temp;
			}
			return $retArr;
		}
		return false;
	}
	
	public function interpretType() {
		if ($this->type==1) return 'continent';
		if ($this->type==2) return 'region';
		if ($this->type==3) return 'town, village or encampment';
		if ($this->type==4) return 'neighborhood';
		if ($this->type==5) return 'building';
		if ($this->type==5) return 'mobile or temporary';
		return 'undefined';
	}
}
?>
