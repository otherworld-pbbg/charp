<?php
include_once('default_table.php');

class activityTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'activity_log';
		$this->field_list = array('uid', 'userFK', 'timestamp');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
