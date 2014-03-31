<?php

include_once('lib/database.php');
include_once('lib/encrypt.php');
include_once('lib/management.php');

include_once('models/entity/model.php');
include_once('models/entity/site.php');
include_once('models/entity/user.php');
include_once('models/entity/page.php');
include_once('models/entity/post.php');
include_once('models/entity/plugin.php');
include_once('models/entity/template.php');
include_once('models/entity/updater.php');
include_once('models/entity/log.php');
include_once('models/entity/authenticate.php');

include_once('models/service/service.php');
include_once('models/service/pageService.php');
include_once('models/service/postService.php');
include_once('models/service/templateService.php');

class core {
	protected $_TYPE = null;
	protected $_ACTION = null;
	protected $_PARENT = null;
	protected $_CHILD = null;
	protected $_FILTER = null;
	protected $_USERPAGE = "user";
	protected $_CONN = null;
	protected $_LINKFORMAT = "";
	protected $_LOG = null;
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
		
		$this->addToScope($this);
		
		//Initialize the services
		$pageService = new pageService($this->_CONN, $this->_LOG);
		$postService = new postService($this->_CONN, $this->_LOG);
		$templateService = new templateService($this->_CONN, $this->_LOG);
		
		//Add services to scope
		$this->addToScope($pageService);
		$this->addToScope($postService);
		$this->addToScope($templateService);
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
	
	public function get_TYPE() {
		return $this->_TYPE;
	}
	
	public function get_ACTION() {
		return $this->_ACTION;
	}
	
	public function get_FILTER() {
		return $this->_FILTER;
	}
		
	public function get_CONN() {
		return $this->_CONN;
	}
	
	public function _LINKFORMAT() {
		return $this->_LINKFORMAT;
	}
	
	public function get_LOG() {
		return $this->_LOG;
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
	 * Loads the plugin files
	 * 
	 * @param $context		The controller that called this function
	 */
	public function loadPlugins($context) {
		
		$sql = "SELECT * FROM plugins ORDER BY plugin_created DESC";
		$result = $this->_CONN->query($sql);
		
		if ($result !== false && mysqli_num_rows($result) > 0 ) {
			while($row = mysqli_fetch_assoc($result) ) {
		
				
				$file = stripslashes($row['plugin_file']);
				$path = stripslashes($row['plugin_path']);
				$className = substr($file, 0, strpos($file, ".php"));
				
				//Include the plugin class, initiate it and add it to the scope
				include_once(PLUGIN_PATH . "/" . $path . "/" . $file);
				$pluginObj = new $className($context);
				$this->addToScope($pluginObj);
			}
		}
		
		
		
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
		$page = new page($this->_CONN, $this->_LOG);
		$page->buildTable();
	
		$post = new post($this->_CONN, $this->_LOG);
		$post->buildTable();
	
		$template = new template($this->_CONN, $this->_LOG);
		$template->buildTable();
	
		$user = new user($this->_CONN, $this->_LOG);
		$user->buildTable();
	
		$site = new site($this->_CONN, $this->_LOG);
		$site->buildTable();
	
		$log = new log($this->_CONN, $this->_LOG);
		$log->buildTable();
	
		$plugin = new plugin($this->_CONN, $this->_LOG);
		$plugin->buildTable();
		
		$auth = new authenticate($this->_CONN, $this->_LOG);
		$auth->buildTable();
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
		$this->_LOG = new log($this->_CONN, null);
	}
}

?>
