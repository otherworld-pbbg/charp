<?php
include_once('default_table.php');

class chatTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'chats';
		$this->field_list = array('uid', 'location', 'author', 'name', 'summary');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
