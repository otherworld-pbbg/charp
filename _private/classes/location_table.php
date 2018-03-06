<?php
include_once('default_table.php');

class locationTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'locations';
		$this->field_list = array('uid', 'name', 'parent', 'type', 'spawning', 'description');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
