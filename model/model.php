<?php

class model {
	
	protected $conn = null; //Database connection object
	protected $linkFormat = null;
	
	/**
	 * Stores the connection object in a local variable on construction
	 *
	 * @param dbConn The property values
	 */
	public function __construct($dbConn) {
		$this->conn = $dbConn;
		$this->linkFormat = get_linkFormat($dbConn);
	}
	
	/**
	 * Render the forms
	 * 
	 */
	public function render($file) {
		
	}
}

?>