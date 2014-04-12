<?php
/*
example plugin class
Author: JRogaishio
Written: 2014-03-08
*/

class example {

	private $controller;
	
	public function __construct($controller) {
		$this->controller = $controller;
		
		//$this->buildTable();
	}
	
	public function load() {	
		//echo lookupPageIdByLink($this->controller->get_CONN(), $this->controller->get_PARENT());
		//echo $this->controller->get_PARENT();
		//echo $this->controller->getScope('page')->getId();
		
	}
	
	public function buildTable() {
		/*Table structure for table `example plugin` */
		$sql = "CREATE TABLE IF NOT EXISTS `example` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `example_field1` int(16) DEFAULT NULL,
		  `example_field2` int(16) DEFAULT NULL,
		  `example_field3` int(16) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->controller->get_CONN()->query($sql) OR DIE ("Could not build table \"example\"");
	}
	
	
}

?>