<?php
include_once('controllers/core.php');

/**
 * Ferret CMS admin class to create admin management pages
 * 
 * FerretCMS is a simple lightweight content management system using PHP and MySQL.
 * This CMS class is written purely in PHP and JavaScript.
 *
 * @author Jacob Rogaishio
 * 
 */
class admin extends core {
	
	/** 
	 * This function is called whenever the class is first initialized. This takes care of page routing
	 * 
	 */
	public function load () {
		
		//Handle global states such as logging out, etc
		$this->cms_handleState();
		
		//Set the user-name and password off the cookies
		$this->_LOGINTOKEN = (isset($_COOKIE['token']) ? clean($this->_CONN,$_COOKIE['token']) : null);
		
		$this->_AUTH = $this->cms_authUser($this->_LOGINTOKEN);
		
		//user gets
		$this->_USERPAGE = isset( $_GET['p'] ) ? clean($this->_CONN,$_GET['p']) : "home";
		
		//Load the system based on the mode (admin / public)
		if($this->_AUTH) {
			parent::render("siteTop");
			parent::render("siteNav");
			$this->cms_displayWarnings();
			
			//Build the pages section ##################################################################################
			echo "<div class='cms_content'>";
			
			//Build the manager
			switch($this->_TYPE) {
				case "site":
				case "page":
				case "template":
				case "customkey":
				case "plugin":
				case "post":
				case "account":
				case "permissiongroup":
				case "log":
					$obj = new $this->_TYPE($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER, $this->_AUTH);
					parent::addToScope($obj);
					if($result)
						echo $obj->displayModelList();
					break;
				case "search":
					echo $this->cms_displaySearch();
					break;
				case "updateDisplay":
					$obj = new updater($this->_CONN, $this->_LOG);
					$obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER, $this->_AUTH);
					break;
				case "updater":
					$obj = new updater($this->_CONN, $this->_LOG);
					$obj->update($this->_USER);
					break;
				case "uploader":
					$obj = new uploader($this->_CONN, $this->_LOG);
					$obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER, $this->_AUTH);
					$this->cms_displayAdminUploads();
				
					break;
				default:
					$this->cms_displayMain();
					break;
			}
			/*} else {
				echo "You do not have permissions to do this with '<strong>" . $this->_TYPE . "</strong>'.<br />";
			}*/
			echo "<br /><br /></div>";
		}
	}
	
	/** 
	 * Handle user triggered state changes such as logging out
	 * 
	 */
	private function cms_handleState() {
		if($this->_TYPE == "web_state") {
			//Build the manager
			switch($this->_ACTION) {
				case "logout":
					echo "<h2>You have been successfully logged out!</h2><br />";
					
					//Grab the username from the token for logging. We don't have the login set yet before we havent authenticated
					if(isset($_COOKIE['token'])) {
						$userSQL = "SELECT * FROM account WHERE account_token='" . clean($this->_CONN,$_COOKIE['token']) . "';";
						$userResult = $this->_CONN->query($userSQL);
						if ($userResult !== false && mysqli_num_rows($userResult) > 0 ) {
							$userData = mysqli_fetch_assoc($userResult);
							$this->_LOG->trackChange("account", 'log_out',$userData['id'], $userData['account_login'], "logged out");
						}					
					}

					//Set the login cookies to expire NOW and unset them
					setcookie("token", "", time()-3600); 
					unset($_COOKIE['token']);
				break;
			default:
				echo "There was an error when trying to change the state...<br />Perhaps someone should stop editing the URL...";
				break;
			}
		}
	}

	/**
	 * Function to authenticate the user against the DB
	 *
	 * @param $token	An encrypted random string used for cookies and saved sessions
	 *
	 * @return Returns boolean true or false on authentication success or failure
	 *
	 */
	private function cms_authUser($token) {
		$authObj = new authenticate($this->_CONN, $this->_LOG);
		
		$timeRemain = $authObj->checkIP();
		
		if($timeRemain != 0) {
			//Display the login manager if the auth failed
			parent::render("siteLogin");
			
			echo "<br /><p class='cms_warning'>You have entered your username or password incorrectly too many times.<br />
					Please wait " . $timeRemain . " minute(s) before attempting to login again.</p>";
			return false;
		}
		else {
			//Build the first user startup form
			if(countRecords($this->_CONN,"account") == 0) {
				$user = new account($this->_CONN, $this->_LOG);
				
				//Display the user management form
				$result = $user->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER, $this->_LOG, $this->_AUTH);
				
				//Check again if a user exists after running the user manager
				if(countRecords($this->_CONN,"account") == 0) {
					echo "<p><strong>Hello</strong> there! I see that you have no users setup.<br />
					Use the above form to create a user account to get started!<br />
					Once you have created your user, you will be sent to the login form. Use your new account to access all the awesomeness!</p><br />";
				} else {
					parent::render("siteLogin");
				}
				return false;
			} else {
				$user = $authObj->authUser($_POST, $token);
				
				//Only display the login if the fields haven't been submitted
				if((!isset($_POST['login_username']) || !isset($_POST['login_password'])) && $user == null) {
					parent::render("siteLogin");
				} else if($user == null) {
					//Display the login manager if the auth failed
					parent::render("siteLogin");
					echo "<br /><p class='cms_warning'>Incorrect user name or password!</p><br /><br />";
					return false;
				} else {
					parent::addToScope($user);
					$this->_USER = $user;
					return true;
				}
			}//On authentication success
		}//On authentication attempt
	}
		
	/** 
	 * Display the results of the search
	 *
	 */
	public function cms_displaySearch() {
		$resultList = "";
		$resultNum = 0;
		echo "Searching <strong>\"" . $this->_ACTION . "\"</strong>...<br />";
		//Page search
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "page", array('page_safeLink', 'page_meta', 'page_title'));
		if ($searchResult !== false && $this->_USER->checkPermission('page', 'read')) {
			$resultList .= "<br /><h3>Results in pages:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=page&action=update&p=".$row['id']."\" title=\"Edit / Manage this page\" alt=\"Edit / Manage this page\" class=\"cms_pageEditLink\" >" . $row['page_title'] . " - " . $row['page_safeLink'] . "</a><br />";
		}
		//Post search
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "post", array('post_title', 'post_content'));
		if ($searchResult !== false && $this->_USER->checkPermission('post', 'read')) {
			$resultList .= "<br /><h3>Results in posts:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=post&action=update&p=".$row['page_id']."&c=". $row['id'] . "\" title=\"Edit / Manage this post\" alt=\"Edit / Manage this post\" class=\"cms_pageEditLink\" >" . $row['post_title'] . "</a><br />";
		}
		//Template search
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "template", array('template_path', 'template_file', 'template_name'));
		if ($searchResult !== false && $this->_USER->checkPermission('template', 'read')) {
			$resultList .= "<br /><h3>Results in templates:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=template&action=update&p=".$row['id']."\" title=\"Edit / Manage this template\" alt=\"Edit / Manage this template\" class=\"cms_pageEditLink\" >" . $row['template_name'] . " - " . $row['template_path'] . "</a><br />";
		}
		//Key search
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "customkey", array('key_name', 'key_value'));
		if ($searchResult !== false && $this->_USER->checkPermission('customkey', 'read')) {
			$resultList .= "<br /><h3>Results in keys:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=key&action=update&p=".$row['id']."\" title=\"Edit / Manage this key\" alt=\"Edit / Manage this key\" class=\"cms_pageEditLink\" >" . $row['key_name'] . " - " . $row['key_value'] . "</a><br />";
		}
		//User search
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "account", array('account_login', 'account_email'));
		if ($searchResult !== false && $this->_USER->checkPermission('account', 'read')) {
			$resultList .= "<br /><h3>Results in users:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=account&action=update&p=".$row['id']."\" title=\"Edit / Manage this user\" alt=\"Edit / Manage this user\" class=\"cms_pageEditLink\" >" . $row['account_login'] . " - " . $row['account_email'] . "</a><br />";
		}
		//Log search
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "log", array('log_info'));
		if ($searchResult !== false && $this->_USER->checkPermission('log', 'read')) {
			$resultList .= "<br /><h3>Results in log:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=log\" class=\"cms_pageEditLink\">User: " . $row['log_user'] . " - Details: " . $row['log_info'] . "</a><br />";
		}
		
		if($resultList!="") {
			echo "Found " . $resultNum . " results!<br />";
			echo $resultList;
		} else {
			echo "No results found. :(";
		}
	
	}
	
			
	/** 
	 * Display the admin homepage. Currently this is a list of all pages.
	 *
	 */
	public function cms_displayMain() {	
		echo ($this->_AUTH ? "Welcome <strong>" . $this->_USER->getLoginname() . "</strong><br /><br />" : "");
		echo "
		<h1>Welcome to the FerretCMS!</h1><br />
		<strong>What is FerretCMS?</strong><br />
		<p class='cms_intro'>
		Glad you asked! FerretCMS is a simple lightweight content management system using PHP and MySQL.<br />
		For updates check out the GitHub repository here:<br />
		<a href='https://github.com/JRogaishio/ferretCMS'>https://github.com/JRogaishio/ferretCMS</a><br />
		Or the creators account here:<br />
		<a href='https://github.com/JRogaishio'>https://github.com/JRogaishio</a><br />
		</p>
		<br />
		<strong>What can this new fangled CMS do?</strong><br />
		<p class='cms_intro'>
			FerretCMS Includes the below features in no particular order:
		</p>
		<ul class='cms_intro'>
				<li>Page management</li>
				<li>Post management</li>
				<li>Template management</li>
				<li>User management</li>
				<li>General website settings management</li>
				<li>Content searching</li>
				<li>Change logging</li>
			</ul>
		
		<br />
		<strong>Cool, anything planned for the future?</strong><br />
		<p class='cms_intro'>
		You bet! Some planned features for FerretCMS are:
		</p>
		<ul class='cms_intro'>
			<li>Plugin management and implementation</li>
		</ul>
		<br />
		";
		
		echo "<strong>By the way, hows my CMS doing?</strong><br />";
		echo "<p class='cms_intro'>Heres some stats!<br />";
		echo "You have <strong>" . countRecords($this->_CONN, "pages","") . "</strong> page(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"posts","") . "</strong> posts(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"templates","") . "</strong> templates(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"users","") . "</strong> users(s)<br />";
		//echo "You have <strong>" . countRecords($this->_CONN,"plugins","") . "</strong> plugins(s)<br />";
		echo "</p>";
	}
	
	/**
	 * Display any global warnings such as missing homepage, etc
	 *
	 */
	public function cms_displayWarnings() {
		//Make sure a homepage is set
		$pageSQL = "SELECT * FROM page WHERE page_isHome=1;";
		$pageResult = $this->_CONN->query($pageSQL);
		
		if (($pageResult == false || mysqli_num_rows($pageResult) == 0) && countRecords($this->_CONN,"users") != 0 && $this->_AUTH == true)
			echo "<span class='cms_warning'>A homepage is missing! Please set a homepage!</span><br />";

	}
}

?>

