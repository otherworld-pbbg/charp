<?php
include_once('default_table.php');

class userTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'users';
		$this->field_list = array('uid', 'username', 'passhash', 'passhash2', 'email', 'joined');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
