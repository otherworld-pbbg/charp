<?php
require_once("custom_error.php");

//This assumes there's only one database. If there's more dbs in the future, it can be extended
class defaultTable {
	protected $mysqli;
	protected $table_name;
	protected $field_list;
	protected $data_array;
	protected $num_rows;
	protected $errors;
	
	public function __construct($mysqli) {
		$this->mysqli = $mysqli;
		$this->table_name = 'default';
		$this->field_list = array('column1', 'column2', 'column3');
		$this->field_list['column1'] = array('pkey' => 'y');
		
		var_dump($this->field_list);
	}
	
	public function getData($where = NULL, $limit = NULL, $order = NULL, $countOnly = false) {
		$this->data_array = array();
		$this->num_rows = 0;
		
		//where is either empty or contains AND and or OR statements
		if (empty($where)) {
			$where_str = NULL;
		}
		else {
			$where_str = "WHERE $where";
		}
		
		if (empty($limit)) {
			$limit_str = NULL;
		}
		else {
			$limit_str = "LIMIT $limit";
		}
		
		if (empty($order)) {
			$order_str = NULL;
		}
		else {
			$order_str = "ORDER BY $order";
		}
		
		$sql = "SELECT count(*) FROM $this->table_name $where_str $limit_str";
		$res = $this->mysqli->query($sql) or trigger_error('SQL', E_USER_ERROR);
		if ($res->num_rows>0) {
			$row = $res->fetch_row();
			$this->num_rows = $row[0];
		}
		else return;
		
		if ($countOnly) return $this->num_rows;
		
		$sql = "SELECT * FROM $this->table_name $where_str $order_str $limit_str";
		$res = $this->mysqli->query($sql) or trigger_error('SQL', E_USER_ERROR);
		if ($res->num_rows>0) {
			while ($row = $res->fetch_assoc()) {
				$this->data_array[] = $row;
			}
			return $this->data_array;
		}
		else return;
	}//end getData
	
	function insertRecord($field_array) {
		$this->errors = array();
		
		$field_list = $this->field_list;
		
		foreach ($field_array as $key => $fieldvalue) {
			if (!in_array($key, $field_list)) {
				unset($field_array[$key]);
			}//if
		}//foreach
		
		if (empty($field_array)) {
			$e = new CustomError('empty_data');
			$this->errors[] = $e;
			return;
		}
		$sql = "INSERT INTO $this->table_name SET ";
		foreach ($field_array as $item => $value) {
			$sql .= "$item='$value', ";
		}//foreach
		$sql = rtrim($sql, ', ');
		
		$res = $this->mysqli->query($sql);
		if ($this->mysqli->errno<>0) {
			if ($this->mysqli->errno == 1062) {
				$e = new CustomError('duplicate_id');
				$this->errors[] = $e;
			}
			else trigger_error('SQL', E_USER_ERROR);
		}
		else if ($this->mysqli->affected_rows==1) return $this->mysqli->insert_id;
		return;
	}
	
	function updateRecord($field_array) {
		$this->errors = array();
		
		$field_list = $this->field_list;
		foreach ($field_array as $field => $fieldvalue) {
			if (!in_array($field, $field_list)) {
				unset($field_array[$field]);
			}//if
		}//foreach
		
		if (empty($field_array)) {
			$e = new CustomError('empty_data');
			$this->errors[] = $e;
			return;
		}
		
		$where = NULL;
		$update = NULL;
		foreach ($field_array as $item => $value) {
			if (isset($field_list[$item]['pkey'])) {
				$where .= "$item='$value' AND ";
			}
			else {
				$update .= "$item='$value', ";
			}//if
		}//foreach
		
		if (empty($where)) {
			$e = new CustomError('no_pkey');
			$this->errors[] = $e;
			return;
		}
		if (empty($update)) {
			$e = new CustomError('empty_data');
			$this->errors[] = $e;
			return;
		}
		
		$where = rtrim($where, ' AND ');
		$update = rtrim($update, ', ');
		
		$sql = "UPDATE $this->table_name SET $update WHERE $where";
		$success = $this->mysqli->query($sql) or trigger_error('Update failed', E_USER_ERROR);
		
		return $success;
	}
	
	function deleteRecord($field_array) {
		$this->errors = array();
		
		$field_list = $this->field_list;
		$where = NULL;
		foreach ($field_array as $item => $value) {
			if (isset($field_list[$item]['pkey'])) {
				$where .= "$item='$value' AND ";
			}//if
		}//foreach
		
		if (empty($where)) {
			$e = new CustomError('no_pkey');
			$this->errors[] = $e;
			return;
		}
		
		$where = rtrim($where, ' AND ');
		
		$sql = "DELETE FROM $this->table_name WHERE $where";
		$success = $this->mysqli->query($sql) or trigger_error('Delete failed', E_USER_ERROR);
		
		return $success;
	}//deleteRecord
	
	function getErrors() {
		return $this->errors;
	}
}//end class
?>
