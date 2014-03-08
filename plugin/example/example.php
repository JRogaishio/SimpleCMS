<?php
/*
Board class
Author: Twitch2641
Written: 2013-03-28

Overview:
This class takes 1 parameter (The post ID) when created.
This class then builds all the comments to that particular post and all subcomments.
I am using recursive functions to build an "indented" format of comments and not a simple list.
*/

class example {

	private $controller;
	
	private $pageId;
	
	public function __construct($controller) {
		$this->controller = $controller;
		
		//$this->buildTable();
	}
	
	/*
	Initializer function! Woo!
	*/
	public function load() {	
		//echo lookupPageIdByLink($this->controller->get_CONN(), $this->controller->get_PARENT());
		//echo $this->controller->get_PARENT();
		//echo $this->controller->getScope('page')->getId();
		
	}
	
	public function buildTable() {
		/*Table structure for table `pages` */
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