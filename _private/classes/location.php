<?php
//type 1 - continent, 2 - region, 3 - town, village or camp, 4 - district, 5 - house or shop, 0 - traveling or temporary

class Location {
	private $mysqli;
	private $id;
	private $name;
	private $parent;
	private $type;
	private $description;
	private $spawning;
	
	public function __construct($mysqli, $id=0, $name='', $parent=0, $type=0, $description='', $spawning=0) {
		$this->mysqli = $mysqli;
		$this->id = $id;
		$this->name = $name;
		$this->parent = $parent;
		$this->type = $type;
		$this->description = $description;
		$this->spawning = $spawning;
		
		if ($id>0&&$type==0) $this->fetchFromDB();//Skip fetching if all relevant information has been passed to constructor
		if ($id==0&&$type>0) $this->addToDB();
	}
	
	private function addToDB() {
		$sql = "INSERT INTO `locations` (`uid`, `name`, `parent`, `type`, `spawning`, `description`) VALUES (" .
			"'" . $this->id				. "', ".
			"'" . $this->name			. "', ".
			"'" . $this->parent			. "', ".
			"'" . $this->type			. "', ".
			"'" . $this->spawning		. "', ".
			"'" . $this->description	. ")";
		$this->mysqli->query($sql);
		if ($this->mysqli->affected_rows == 1) return true;
		$this->id = 0;
		return false;
	}
	
	private function fetchFromDB() {
		$sql = "SELECT `name`, `parent`, `type`, `description`, `spawning` FROM `locations` WHERE `uid`=$this->id LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows==1) {
			$arr = $res->fetch_assoc();
			$this->name = $arr['name'];
			$this->parent = $arr['parent'];
			$this->type = $arr['type'];
			$this->description = $arr['description'];
			$this->spawning = $arr['spawning'];
			return true;
		}
		$this->id = 0;//If fetch failed
		return false;
	}
	
	public function checkIfExists() {
		return $this->fetchFromDB();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		if ($this->id==0) return 'The Limbo';
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
		$sql = "UPDATE `locations` SET `spawning`=$new WHERE `uid`=" . $this->id . " LIMIT 1";
	}
	
	public function getChildLocations() {
		$sql = "SELECT `uid`, `name`, `description`, `type`, `spawning` FROM `locations` WHERE `parent`=$this->id ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$retArr = array();
			while ($arr = $res->fetch_assoc()) {
				$temp = new Location(
					$this->mysqli,
					$arr["uid"],
					$arr['name'],
					$this->id,
					$arr['type'],
					$arr['description'],
					$arr['spawning']);
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
		$sql = "SELECT `uid`, `author`, `name`, `summary` FROM `chats` WHERE `location`=" . $this->id . " ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$retArr = array();
			while ($arr = $res->fetch_assoc()) {
				$temp = new Chat($this->mysqli, $arr['uid'], $this->id, $arr["author"], $arr["name"], $arr["summary"]);
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
