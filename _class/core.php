<?php
class core {
	private $_SCOPE;
	
	/**
	 * Gets the current scope index provided an object is defined
	 */
	public function getScope($i) {
		$ret = null;
		if (is_object ( $this->_SCOPE [$i] ))
			$ret = $this->_SCOPE [$i];
		
		return $ret;
	}
	
	/**
	 * Adds an object to the scope
	 */
	public function addToScope($obj) {
		if (is_object ( $obj ))
			$this->_SCOPE [get_class ( $obj )] = $obj;
	}
}

?>