<?php
class Db_factory {
	const HOST_SITE = DB_HOST_SITE;
	const HOST_USERNAME = DB_HOST_USERNAME;
	const HOST_PASSWORD = DB_HOST_PASSWORD;
	
	private static $databases = array();
	
	//private static functions
	private static function give_db_username($dbTitle) {
		return self::HOST_USERNAME . "_$dbTitle";
	}
	private static function give_db_name($dbTitle) {
		return self::give_db_username($dbTitle);
	}
	
	//public static functions
	static function get($dbTitle) {
		if (!isset(self::$databases[$dbTitle])) {
			$username = self::give_db_username($dbTitle);
			$database = self::give_db_name($dbTitle);
			
			self::$databases[$dbTitle] = new mysqli(self::HOST_SITE,$username,HOST_PASSWORD,$database);
		}
		return self::$databases[$dbTitle];
	}
}

class Db_Result {
	private $stmt, $rowNum, $row;
	
	function __construct($stmt) {
		$this->stmt = $stmt;
		$this->rowNum = 0;
		$this->stmt->store_result();
		
		$metadata = $this->stmt->result_metadata();
		$this->params = array();
		$this->row = array();
		
		while ($field = $metadata->fetch_field()) {
			$params[] =& $this->row[$field->name];
		}
		
		call_user_func_array( array($this->stmt, 'bind_result'), $params );
	}
	
	//private functions
	function fetch_assoc() {
		if (++$this->rowNum > $this->stmt->num_rows) {
			return null;
		}
		
		$this->stmt->fetch();
		
		return $this->row;
	}
}
?>
