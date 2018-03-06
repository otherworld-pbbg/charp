<?php
include_once('default_table.php');

class seenMsgTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'seen_msg';
		$this->field_list = array('uid', 'viewer', 'chat', 'msg');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
