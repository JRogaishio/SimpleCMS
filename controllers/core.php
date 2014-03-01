<?php

include_once('lib/database.php');
include_once('lib/encrypt.php');
include_once('lib/management.php');

include_once('models/model.php');
include_once('models/site.php');
include_once('models/user.php');
include_once('models/page.php');
include_once('models/post.php');
include_once('models/plugin.php');
include_once('models/template.php');
include_once('models/updater.php');
include_once('models/log.php');

class core {
	protected $_TYPE = null;
	protected $_ACTION = null;
	protected $_PARENT = null;
	protected $_CHILD = null;
	protected $_FILTER = null;
	protected $_USERPAGE = "user";
	protected $_CONN = null;
	protected $_LINKFORMAT = "";
	protected $_LOG;
	//Login stuff
	protected $_AUTH = false;
	protected $_USER = null;
	protected $_LOGINTOKEN = null;
	protected $_SCOPE;
	
	/**
	 * Core constructor
	 */
	public function __construct($connType) {
		$this->connect($connType);
		
		//Admin Gets
		$this->_TYPE = isset( $_GET['type'] ) ? clean($this->_CONN,$_GET['type']) : null;
		$this->_ACTION = isset( $_GET['action'] ) ? clean($this->_CONN,$_GET['action']) : null;
		$this->_PARENT = isset( $_GET['p'] ) ? clean($this->_CONN,$_GET['p']) : null;
		$this->_CHILD = isset( $_GET['c'] ) ? clean($this->_CONN,$_GET['c']) : null;
		$this->_FILTER = isset( $_GET['f'] ) ? clean($this->_CONN,$_GET['f']) : null;
	}
	
	/**
	 * Returns the 'p' get data in the URL
	 *
	 * @return Returns get p= data from the url
	 *
	 */
	public function get_PARENT() {
		return $this->_PARENT;
	}
	
	/**
	 * Returns the 'c' get data in the URL
	 *
	 * @return Returns get c= data from the url
	 *
	 */
	public function get_CHILD() {
		return $this->_CHILD;
	}
	
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
	
	/**
	 * Renders a specific PHP file
	 */
	public function render($name) {
		if(isset($name) && $name != null && strpos($name, " ") == null) {
			$file = "views/" . $name . ".php";
			
			if((@include $file) === false)
			{
				echo "Cannot include file " . $file . "!";
			}
		}
	}
	
	/**
	 * Build the tables required by the CMS. They will only build if the table doesn't exist.
	 *
	 */
	private function buildDB() {
		$page = new page($this->_CONN);
		$page->buildTable();
	
		$post = new post($this->_CONN);
		$post->buildTable();
	
		$template = new template($this->_CONN);
		$template->buildTable();
	
		$user = new user($this->_CONN);
		$user->buildTable();
	
		$site = new site($this->_CONN);
		$site->buildTable();
	
		$log = new log($this->_CONN);
		$log->buildTable();
	
		$plugin = new plugin($this->_CONN);
		$plugin->buildTable();
	}
	
	/**
	 * Connect to the database defined in config.php
	 *
	 * @param $connType		Can be either user or admin and is used to prevent unnecessary SQL database builds
	 *
	 */
	public function connect($connType = null) {
	
		$this->_CONN = new mysqli(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysqli_error());
	
		//Create the database if it doesn't exist
		$dbCreate = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`;";
		$this->_CONN->query($dbCreate) OR DIE ("Could not build database!");
	
		//Connect to our shiney new database
		$this->_CONN->select_db(DB_NAME) or die("Could not select database. " . mysqli_error());
	
		//Attempt to build the DB if you aren't authenticated and we are on the admin page
		if(!isset($_COOKIE['token']) && $connType == "admin") {
			$this->buildDB();
		}
			
		//Create a logging object
		$this->_LOG = new log($this->_CONN);
	}
}

?>
