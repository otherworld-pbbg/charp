<?php
include_once('default_table.php');

class pendingUsersTable extends defaultTable
{
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'pending_users';
		$this->field_list = array('uid', 'username', 'passhash', 'email', 'joined', 'activation', 'type', 'userid');
		$this->field_list['uid'] = array('pkey' => 'y');
	}
}
?>
