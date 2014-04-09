<?php

class model {
	
	protected $conn = null; //Database connection object
	protected $log = null;
	protected $linkFormat = null;
	
	/**
	 * Stores the connection object in a local variable on construction
	 *
	 * @param $dbConn	The property values
	 * @param $dbLog	The log object used by the system
	 */
	public function __construct($dbConn, $dbLog) {
		$this->conn = $dbConn;
		$this->log = $dbLog;
		$this->linkFormat = get_linkFormat($dbConn);
	}
}

?>
