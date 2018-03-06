<?php
include_once('default_table.php');

class memoTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'memos';
		$this->field_list = array('uid', 'userid', 'subject', 'contents');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
