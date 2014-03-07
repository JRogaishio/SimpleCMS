<?php
include_once('controllers/core.php');

/**
 * Ferret CMS Main class to create admin pages and live content pages
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
					$obj = new site($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER);
					parent::addToScope($obj);
					
					if($result) 
						echo $this->cms_displayAdminSite();
					
					break;
				case "siteDisplay":
					echo $this->cms_displayAdminSite();
					break;
				case "page":
					$obj = new page($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER);
					parent::addToScope($obj);
					if($result)
						echo $this->cms_displayAdminPages();

					break;
				case "pageDisplay":
					echo $this->cms_displayAdminPages();
					break;
				case "template":
					$obj = new template($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER);
					parent::addToScope($obj);
					if($result)
						echo $this->cms_displayAdminTemplates();

					break;
				case "templateDisplay":
					echo $this->cms_displayAdminTemplates();
					break;
				case "plugin":
					$obj = new plugin($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER);
					parent::addToScope($obj);
					if($result)
						echo $this->cms_displayAdminPlugins();
				
					break;
				case "pluginDisplay":
					echo $this->cms_displayAdminPlugins();
					break;	
				case "post":
					$obj = new post($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER);
					parent::addToScope($obj);
					if($result)
						echo $this->cms_displayAdminPosts();

					break;
				case "postDisplay":
					echo $this->cms_displayAdminPosts();
					break;
				case "user":
					$obj = new user($this->_CONN, $this->_LOG);
					$result = $obj->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER, $this->_AUTH);
					parent::addToScope($obj);
					if($result)
						echo $this->cms_displayAdminUsers();

					break;
				case "userDisplay":
					echo $this->cms_displayAdminUsers();
					break;
				case "search":
					echo $this->cms_displaySearch();
					break;
				case "log":
					echo $this->cms_displayLog();
					break;
				case "updateDisplay":
					$obj = new updater($this->_CONN, $this->_LOG);
					$obj->displayManager();
					break;
				case "update":
					$obj = new updater($this->_CONN, $this->_LOG);
					$obj->update($this->_USER);
					break;
				default:
					$this->cms_displayMain();
					break;
			}
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
						$userSQL = "SELECT * FROM users WHERE user_token='" . clean($this->_CONN,$_COOKIE['token']) . "';";
						$userResult = $this->_CONN->query($userSQL);
						if ($userResult !== false && mysqli_num_rows($userResult) > 0 ) {
							$userData = mysqli_fetch_assoc($userResult);
							$this->_LOG->trackChange("user", 'log_out',$userData['id'], $userData['user_login'], "logged out");
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
			if(countRecords($this->_CONN,"users") == 0) {
				$user = new User($this->_CONN, $this->_LOG);
				
				//Display the user management form
				echo $user->displayManager($this->_ACTION, $this->_PARENT, $this->_CHILD, $this->_USER, $log, $this->_AUTH);
				
				//Check again if a user exists after running the user manager
				if(countRecords($this->_CONN,"users") == 0) {
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
	 * Display the site manager
	 *
	 */
	public function cms_displayAdminSite() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=siteDisplay">Site</a><br /><br />';
		
		$siteSQL = "SELECT * FROM sites ORDER BY id DESC";
		$siteResult = $this->_CONN->query($siteSQL);
	
		if ($siteResult !== false && mysqli_num_rows($siteResult) > 0 ) {
			while($row = mysqli_fetch_assoc($siteResult) ) {
				
				$name = stripslashes($row['site_name']);

				echo "
				<div class=\"site\">
					<h2>
					Site: <a href=\"admin.php?type=site&action=update&p=".$row['id']."\" class=\"cms_siteEditLink\" >$name</a>
					</h2>
				</div>";
			}
		} else {
			echo "
			<p>
				No sites found!
			</p>";
		}
	}	
		
	/** 
	 * Display the list of all pages
	 *
	 */
	public function cms_displayAdminPages() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pageDisplay">Page List</a><br /><br />';
		
		$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
		$pageResult = $this->_CONN->query($pageSQL);
	
		if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 ) {
			while($row = mysqli_fetch_assoc($pageResult) ) {
				
				$title = stripslashes($row['page_title']);
				$safeLink = stripslashes($row['page_safeLink']);

				echo "
				<div class=\"page\">
					<h2>
					<a href=\"admin.php?type=page&action=update&p=".$row['id']."\" " . ($row['page_isHome']==1 ? "id='cms_homepageMarker'":"") . " title='" . ($row['page_isHome']==1 ? "Edit / Manage the homepage":"Edit / Manage this page") . "' class=\"cms_pageEditLink\" >$title</a>
					</h2>
					<p>" . SITE_ROOT . $safeLink . "</p>
				</div>";
			}
		} else {
			echo "
			<p>
				No pages found!
			</p>";
		}
	}
	
	/** 
	 * Display the list of all templates
	 *
	 */
	public function cms_displayAdminTemplates() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=templateDisplay">Template List</a><br /><br />';
		
		$templateSQL = "SELECT * FROM templates ORDER BY template_created DESC";
		$templateResult = $this->_CONN->query($templateSQL);
	
		if ($templateResult !== false && mysqli_num_rows($templateResult) > 0 ) {
			while($row = mysqli_fetch_assoc($templateResult) ) {
				
				$name = stripslashes($row['template_name']);
				$file = stripslashes($row['template_file']);
				$path = stripslashes($row['template_path']);
				
				echo "
				<div class=\"template\">
					<h2>
					<a href=\"admin.php?type=template&action=update&p=".$row['id']."\" title=\"Edit / Manage this template\" alt=\"Edit / Manage this template\" class=\"cms_pageEditLink\" >$name</a>
					</h2>
					<p>" . TEMPLATE_PATH . "/" . $path . "/" . $file . "</p>
				</div>";

			}
		} else {
			echo "
			<p>
				No templates found!
			</p>";
		}
	
	}
	
	/**
	 * Display the list of all plugins
	 *
	 */
	public function cms_displayAdminPlugins() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pluginDisplay">Plugin List</a><br /><br />';
	
		$sql = "SELECT * FROM plugins ORDER BY plugin_created DESC";
		$result = $this->_CONN->query($sql);
	
		if ($result !== false && mysqli_num_rows($result) > 0 ) {
			while($row = mysqli_fetch_assoc($result) ) {
	
				$name = stripslashes($row['plugin_name']);
				$file = stripslashes($row['plugin_file']);
				$path = stripslashes($row['plugin_path']);
	
				echo "
				<div class=\"plugin\">
					<h2>
					<a href=\"admin.php?type=plugin&action=update&p=".$row['id']."\" title=\"Edit / Manage this plugin\" alt=\"Edit / Manage this plugin\" class=\"cms_pageEditLink\" >$name</a>
						</h2>
						<p>" . PLUGIN_PATH . "/" . $path . "/" . $file . "</p>
				</div>";
	
			}
		} else {
			echo "
			<p>
				No plugins found!
			</p>";
		}
	
	}	
	
	/** 
	 * Display the list of all posts and their respective pages
	 *
	 */
	public function cms_displayAdminPosts() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=postDisplay">Post List</a><br /><br />';
	
		$postSQL = "SELECT * FROM posts ORDER BY page_id ASC";
		$postResult = $this->_CONN->query($postSQL);
		$lastPageName = "";
		
		if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
			while($row = mysqli_fetch_assoc($postResult) ) {
				
				if($lastPageName != lookupPageNameById($this->_CONN, $row['page_id'])) {
					//If we aren't on the first page in the list, add some line breaks inbetween page lists.
					if($lastPageName != "")
						echo "<br /><br />";
					
					$lastPageName = lookupPageNameById($this->_CONN, $row['page_id']);
					echo "<h1 class='cms_pageTitle'>" . $lastPageName . "</h1>";
				}
				
				$title = stripslashes($row['post_title']);
				$postDate = stripslashes($row['post_date']);

				echo "
				<div class=\"page\">
				<h3>
				<a href=\"admin.php?type=post&action=update&p=".$row['page_id']."&c=".$row['id']."\" title=\"Edit / Manage this post\" alt=\"Edit / Manage this page\" class=\"cms_pageEditLink\" >$title</a>
				</h3>
				<p>
				" . $postDate . "
				</p>
				</div>";

			}
		} else {
			echo "
			<p>
			No posts found!<br /><br />
			</p>";
		}
		
	}	
	
	/** 
	 * Display the list of all users
	 *
	 */
	public function cms_displayAdminUsers() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=userDisplay">User List</a><br /><br />';
		
		$userSQL = "SELECT * FROM users ORDER BY user_created DESC";
		$userResult = $this->_CONN->query($userSQL);
	
		if ($userResult !== false && mysqli_num_rows($userResult) > 0 ) {
			while($row = mysqli_fetch_assoc($userResult) ) {
				
				$username = stripslashes($row['user_login']);
				$email = stripslashes($row['user_email']);
				
				echo "
				<div class=\"user\">
					<h2>
					<a href=\"admin.php?type=user&action=update&p=".$row['id']."\" title=\"Edit / Manage this user\" alt=\"Edit / Manage this user\" class=\"cms_pageEditLink\" >$username</a>
					</h2>
					<p>" . $email . "</p>
				</div>";

			}
		} else {
			echo "
			<p>
				No users found!
			</p>";
		}
	
	}
	
	/** 
	 * Display the results of the search
	 *
	 */
	public function cms_displaySearch() {
		$resultList = "";
		$resultNum = 0;
		echo "Searching <strong>\"" . $this->_ACTION . "\"</strong>...<br />";
	
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "pages", array('page_safeLink', 'page_meta', 'page_title'));
		if ($searchResult !== false) {
			$resultList .= "<br /><h3>Results in pages:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=page&action=update&p=".$row['id']."\" title=\"Edit / Manage this page\" alt=\"Edit / Manage this page\" class=\"cms_pageEditLink\" >" . $row['page_title'] . " - " . $row['page_safeLink'] . "</a><br />";
		}
		
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "posts", array('post_title', 'post_content'));
		if ($searchResult !== false) {
			$resultList .= "<br /><h3>Results in posts:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=post&action=update&p=".$row['page_id']."&c=". $row['id'] . "\" title=\"Edit / Manage this post\" alt=\"Edit / Manage this post\" class=\"cms_pageEditLink\" >" . $row['post_title'] . "</a><br />";
		}
		
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "templates", array('template_path', 'template_file', 'template_name'));
		if ($searchResult !== false) {
			$resultList .= "<br /><h3>Results in templates:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=template&action=update&p=".$row['id']."\" title=\"Edit / Manage this template\" alt=\"Edit / Manage this template\" class=\"cms_pageEditLink\" >" . $row['template_name'] . " - " . $row['template_path'] . "</a><br />";
		}
		
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "users", array('user_login', 'user_email'));
		if ($searchResult !== false) {
			$resultList .= "<br /><h3>Results in users:</h3>";
			$resultNum += mysqli_num_rows($searchResult);
			while($row = mysqli_fetch_assoc($searchResult))
				$resultList .="<a href=\"admin.php?type=user&action=update&p=".$row['id']."\" title=\"Edit / Manage this user\" alt=\"Edit / Manage this user\" class=\"cms_pageEditLink\" >" . $row['user_login'] . " - " . $row['user_email'] . "</a><br />";
		}
		
		$searchResult = searchTable($this->_CONN, $this->_ACTION,  "log", array('log_info'));
		if ($searchResult !== false) {
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
	 * Display the system log
	 *
	 */
	public function cms_displayLog() {
		$resultList = "";
		$logSQL = "SELECT * FROM log ORDER BY log_created DESC;";
		$logResult = $this->_CONN->query($logSQL);
		
		if ($logResult !== false && mysqli_num_rows($logResult) > 0 ) {
			$resultList .= "
			<h3>Results in log:</h3>
			<br /><br />
			<table border=1>
			<tr><th>User</th><th>Details</th><th>Date</th><th>IP Address</th></tr>
			";
			while($row = mysqli_fetch_assoc($logResult))
				$resultList .= "<tr><td>" . $row['log_user'] . "</td><td>" . $row['log_info'] . "</td><td>" . $row['log_date'] . "</td><td>". $row['log_remoteIp'] . "</td></tr>";
			
			$resultList .= "</table>";
			
			echo $resultList;
			
		} else {
			echo "No logs found?";
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
		$pageSQL = "SELECT * FROM pages WHERE page_isHome=1;";
		$pageResult = $this->_CONN->query($pageSQL);
		
		if (($pageResult == false || mysqli_num_rows($pageResult) == 0) && countRecords($this->_CONN,"users") != 0 && $this->_AUTH == true)
			echo "<span class='cms_warning'>A homepage is missing! Please set a homepage!</span><br />";
	
	
	}
	
	
}

?>

