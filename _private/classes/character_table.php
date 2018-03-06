<?php
include_once('default_table.php');

class characterTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'characters';
		$this->field_list = array('uid', 'name', 'owner', 'sex', 'location');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
