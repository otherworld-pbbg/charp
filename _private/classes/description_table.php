<?php
include_once('default_table.php');

class descriptionTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'descriptions';
		$this->field_list = array('uid', 'charid', 'contents');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
