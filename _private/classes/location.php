<?php
//type 1 - continent, 2 - region, 3 - town, village or camp, 4 - district, 5 - house or shop, 0 - traveling or temporary

class Location {
	private $mysqli;
	private $id;
	private $name;
	private $parent;
	private $type;
	private $description;
	
	public function __construct($mysqli, $id=0, $name='', $parent=0, $type=0, $description='') {
		$this->mysqli = $mysqli;
		$this->id = $id;
		$this->name = $name;
		$this->parent = $parent;
		$this->type = $type;
		$this->description = $description;
		
		if ($id>0) $this->fetchFromDB();
	}
	
	private function fetchFromDB() {
		$sql = "SELECT `name`, `parent`, `type`, `description` FROM `locations` WHERE `uid`=$this->id LIMIT 1";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows==1) {
			$arr = $res->fetch_assoc();
			$this->name = $arr['name'];
			$this->parent = $arr['parent'];
			$this->type = $arr['type'];
			$this->description = $arr['description'];
			return true;
		}
		return false;
	}
	
	public function checkIfExists() {
		return $this->fetchFromDB();
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		if ($this->id==0) return 'off-game';
		if ($this->name == '') return '(unnamed)';
		return $this->name;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getChildLocations() {
		$sql = "SELECT `uid`, `name`, `description`, `type` FROM `locations` WHERE `parent`=$this->id ORDER BY `uid`";
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
					$arr['description']);
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
}
?>
