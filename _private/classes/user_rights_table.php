<?php
include_once('default_table.php');

class userRightsTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'user_rights';
		$this->field_list = array('uid', 'userid', 'permission', 'value');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
