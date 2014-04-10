<?php

class service {
	
	protected $conn = null; //Database connection object
	protected $log = null;
	protected $linkFormat = null;
	protected $model = null;
	
	/**
	 * Stores the connection object in a local variable on construction
	 *
	 * @param $dbConn	The database conenction object
	 * @param $dbLog	The database log object
	 */
	public function __construct($dbConn, $dbLog) {
		$this->conn = $dbConn;
		$this->log = $dbLog;
		$this->linkFormat = get_linkFormat($dbConn);
	}
	
	/**
	 * Resets the model this service is referencing
	 * 
	 * @param $obj The new object to set as the service context
	 */
	public function setContext($obj) {
		$this->model = $obj;
	}
	
}

?>
