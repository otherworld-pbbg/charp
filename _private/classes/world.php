<?php
class World {
	private $mysqli;
	
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
	}
	
	public function getLocsByType($type) {
		$sql = "SELECT `uid`, `name`, `description`, `parent` FROM `locations` WHERE `type`=$type ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$retArr = array();
			while ($arr = $res->fetch_assoc()) {
				$temp = new Location(
					$this->mysqli,
					$arr["uid"],
					$arr['name'],
					$arr['parent'],
					$type,
					$arr['description']);
				$retArr[] = $temp;
			}
			return $retArr;
		}
		return false;
	}
	
	public function getStartingLocations() {
		$sql = "SELECT `uid`, `name`, `description`, `parent`, `type` FROM `locations` WHERE `spawning`=1 ORDER BY `uid`";
		$res = $this->mysqli->query($sql);
		if ($res->num_rows>0) {
			$retArr = array();
			while ($arr = $res->fetch_assoc()) {
				$temp = new Location(
					$this->mysqli,
					$arr["uid"],
					$arr['name'],
					$arr['parent'],
					$arr['type'],
					$arr['description'],
					1);//spawning
				$retArr[] = $temp;
			}
			return $retArr;
		}
		return false;
	}
}
?>
