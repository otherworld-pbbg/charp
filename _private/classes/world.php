<?php
class World {
	private $mysqli;
	
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
	}
	
	public function getLocsByType($type) {
		$ltable = new LocationTable($this->mysqli);
		$data = $ltable->getData("`type`=$type", NULL, '`uid` ASC');
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
	
	public function getStartingLocations() {
		$ltable = new LocationTable($this->mysqli);
		$data = $ltable->getData("`spawning`=1", NULL, '`uid` ASC');
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
}
?>
