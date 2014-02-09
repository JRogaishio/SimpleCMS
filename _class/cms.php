<?php
include_once('_lib/database.php');
include_once('_lib/encrypt.php');
include_once('_lib/management.php');

include_once('_class/site.php');
include_once('_class/user.php');
include_once('_class/page.php');
include_once('_class/post.php');
include_once('_class/template.php');

/**
 * Ferret CMS Main class to create admin pages and live content pages
 * 
 * FerretCMS is a simple lightweight content management system using PHP and MySQL.
 * This CMS class is written purely in PHP and JavaScript.
 *
 * @author Jacob Rogaishio
 * 
 */
class cms {

	private $_MODE = "user";
	private $_TYPE = null;
	private $_ACTION = null;
	private $_PARENT = null;
	private $_CHILD = null;
	private $_FILTER = null;
	private $_USERPAGE = "user";
	private $_CONN = null;
	private $_LINKFORMAT = "";
	//Login stuff
	private $_AUTH = false;	
	private $_USER = null;
	private $_LOGINTOKEN = null;
	
	/** 
	 * This function is called whenever the class is first initialized. This takes care of page routing
	 * 
	 * @param $mode		Either admin or user and determines how to display the CMS
	 *
	 */
	public function load ($mode) {
		//Let the CMS know if we are running user or admin rules
		$this->_MODE = $mode;
		
		//Admin Gets
		$this->_TYPE = isset( $_GET['type'] ) ? clean($this->_CONN,$_GET['type']) : null;
		$this->_ACTION = isset( $_GET['action'] ) ? clean($this->_CONN,$_GET['action']) : null;
		$this->_PARENT = isset( $_GET['p'] ) ? clean($this->_CONN,$_GET['p']) : null;
		$this->_CHILD = isset( $_GET['c'] ) ? clean($this->_CONN,$_GET['c']) : null;
		$this->_FILTER = isset( $_GET['f'] ) ? clean($this->_CONN,$_GET['f']) : null;
		$this->_LINKFORMAT = get_linkFormat($this->_CONN);
		//Handle global states such as logging out, etc
		$this->cms_handleState();
		
		//Set the user-name and password off the cookies
		$_LOGINTOKEN = (isset($_COOKIE['token']) ? clean($this->_CONN,$_COOKIE['token']) : null);
	
		if($mode == "admin")
			$this->_AUTH = $this->cms_authUser($_LOGINTOKEN);
		
		//user gets
		$this->_USERPAGE = isset( $_GET['p'] ) ? clean($this->_CONN,$_GET['p']) : "home";
				
		//Load the system based on the mode (admin / public)
		if($this->_AUTH && $mode == "admin") {
			$this->cms_displayTop();
			$this->cms_displayNav();
			$this->cms_displayWarnings();
			
			//Build the pages section ##################################################################################
			echo "<div class='cms_content'>";
				
			//Build the manager
			switch($this->_TYPE) {
				case "site":
					echo $this->cms_displaySiteManager();
					break;
				case "siteDisplay":
					echo $this->cms_displayAdminSite();
					break;
				case "page":
					echo $this->cms_displayPageManager();
					break;
				case "pageDisplay":
					echo $this->cms_displayAdminPages();
					break;
				case "template":
					echo $this->cms_displayTemplateManager();
					break;
				case "templateDisplay":
					echo $this->cms_displayAdminTemplates();
					break;
				case "post":
					echo $this->cms_displayPostManager();
					break;
				case "postDisplay":
					echo $this->cms_displayAdminPosts();
					break;
				case "user":
					echo $this->cms_displayUserManager();
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
				default:
					$this->cms_displayMain();
					break;
			}
			echo "<br /><br /></div>";

		} else if($mode == "user"){
			//User view mode
			$this->load_page($this->_USERPAGE);
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
							logChange($this->_CONN, "user", 'log_out',$userData['id'], $userData['user_login'], "logged out");
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
	 * Build the CMS's top menu
	 * 
	 */
	private function cms_displayTop() {
		echo "
		<div class=\"cms_top\">
			<h1 class=\"cms_title\"><a href=\"#\">{f}</a></h1>
			<div class=\"cms_topItems\">
				<form action='admin.php' method='get'>
				<input type=\"hidden\" name=\"type\" value=\"search\" />
				<input type=\"text\" name=\"action\" value=\"search here\" size=\"25\" class=\"cms_searchBox\" onclick=\"if($(this).val()=='search here'?$(this).val(''):$(this).val());\"/>
				</form>
				<a href=\"admin.php?type=web_state&action=logout\"><span id=\"cms_login\">Log out</span></a> <br />
			</div>	
		</div>
		<div class=\"cms_topSpacer\">&nbsp;</div>
		";
	}
	
	/**
	 * Build the CMS's navigation menu
	 *
	 */
	private function cms_displayNav() {
	
		echo '
			<div class="cms_nav">
				<div class="cms_navItemTitle"><div id="cms_dash" class="cms_icon"></div><a href="admin.php" class="cms_navItemTitleLink">Dashboard</a></div>
				
				<div><div class="cms_navItemTitle"><div id="cms_site" class="cms_icon"></div>Website Manager</div>
					<div class="cms_navItemList" id="cms_navItemList_site">
						<ul>
						<li class="cms_navItem"><a href="admin.php?type=siteDisplay" class="cms_navItemLink">Edit Site</a></li>
						<li class="cms_navItem"><a href="admin.php?type=log" class="cms_navItemLink">View the log</a></li>						
						</ul>
					</div>
				</div>
			
				
				<div><div class="cms_navItemTitle"><div id="cms_page" class="cms_icon"></div>Page Manager</div>
					<div class="cms_navItemList" id="cms_navItemList_page">
						<ul>
						<li class="cms_navItem"><a href="admin.php?type=pageDisplay" class="cms_navItemLink">Edit Pages</a></li>
						<li class="cms_navItem"><a href="admin.php?type=page&action=update&p=new" class="cms_navItemLink">Add a Page</a></li>
						</ul>
					</div>
				</div>
				<div><div class="cms_navItemTitle"><div id="cms_post" class="cms_icon"></div>Post Manager</div>
					<div class="cms_navItemList" id="cms_navItemList_post">
						<ul>
						<li class="cms_navItem"><a href="admin.php?type=postDisplay" class="cms_navItemLink">Edit Posts</a></li>
						<li class="cms_navItem"><a href="admin.php?type=post&action=update&p=' . $this->_PARENT . '&c=new" class="cms_navItemLink">Add a Post</a></li>
						</ul>
					</div>	
				</div>	
				<div><div class="cms_navItemTitle"><div id="cms_template" class="cms_icon"></div>Template Manager</div>
					<div class="cms_navItemList" id="cms_navItemList_template">
						<ul>
						<li class="cms_navItem"><a href="admin.php?type=templateDisplay" class="cms_navItemLink">Edit Templates</a></li>
						<li class="cms_navItem"><a href="admin.php?type=template&action=update&p=new" class="cms_navItemLink">Add a Template</a></li>
						</ul>
					</div>	
				</div>	
					
				<div><div class="cms_navItemTitle"><div id="cms_user" class="cms_icon"></div>User Manager</div>
					<div class="cms_navItemList" id="cms_navItemList_user">
						<ul>
						<li class="cms_navItem"><a href="admin.php?type=userDisplay" class="cms_navItemLink">Edit Users</a></li>
						<li class="cms_navItem"><a href="admin.php?type=user&action=update&p=' . $this->_PARENT . '" class="cms_navItemLink">Add a User</a></li>
						</ul>
					</div>	
				</div>	
					<!--
				<div><div class="cms_navItemTitle"><div id="cms_plug" class="cms_icon"></div>Plugin Manager</div>
					<div class="cms_navItemList" id="cms_navItemList_plug">
						<ul>
						<li class="cms_navItem"><a href="#" class="cms_navItemLink">Edit Plugins</a></li>
						<li class="cms_navItem"><a href="#" class="cms_navItemLink">Add a Plugin</a></li>
						</ul>
					</div>	
				</div>
				-->
				<div><div class="cms_navItemTitle"></div></div>
				
			</div>
	
		';
	
	
		
		
	}
	
	/* 
	 * User to determine if we should show the user create page
	 *
	 */
	private function cms_getNumUsers() {
		$userSQL = "SELECT * FROM users;";
		$userResult = $this->_CONN->query($userSQL);

		$numUser = mysqli_num_rows($userResult);
		return $numUser;
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
		//Check to see if any login info was posted or if a token exists
		if((($token!=null) || (isset($_POST['login_username']) && isset($_POST['login_password']))) && $this->cms_getNumUsers() > 0) {
			if(isset($_POST['login_username']) && isset($_POST['login_password'])) {
				
				$secPass = encrypt(clean($this->_CONN,$_POST['login_password']), get_userSalt($this->_CONN, clean($this->_CONN,$_POST['login_username'])));
			
				$userSQL = "SELECT * FROM users WHERE user_login='" . clean($this->_CONN,$_POST['login_username']) . "' AND user_pass='$secPass';";
			} else {
				$userSQL = "SELECT * FROM users WHERE user_token='$token';";
			}
			
			$userResult = $this->_CONN->query($userSQL);

			//Test to see if the auth was successful
			if ($userResult !== false && mysqli_num_rows($userResult) > 0 ) {
				$userData = mysqli_fetch_assoc($userResult);

				$user = new User($this->_CONN);
				
				//Set the user data
				$user->id = ($userData['id']);
				$user->loginname = ($userData['user_login']);
				$user->password = ($userData['user_pass']);
				$user->salt = ($userData['user_salt']);
				$user->email = ($userData['user_email']);
				$user->isRegistered = ($userData['user_isRegistered']);
	
				//Set the global variable
				$this->_USER = $user;
				
				//30 minute auth time-out
				$timeout = time() + 900; 
				
				$newToken = hash('sha256', (unique_salt() . $user->loginname));
	
				$tokenSQL = "UPDATE users SET user_token = '$newToken' WHERE id=" . $user->id . ";";
				$tokenResult = $this->_CONN->query($tokenSQL) OR DIE ("Could not update user!");
				if(!$tokenResult) {
					echo "<span class='update_notice'>Failed to update login token!</span><br /><br />";
				}
				
				//Create a random cookie based off of the user name and a unique salt
				setcookie("token", $newToken, $timeout); 
				
				//Log that a user logged in. POST data is only set on the initial login
				if(isset($_POST['login_username']) && isset($_POST['login_password'])) {
					logChange($this->_CONN, "user", 'log_in',$user->id,$user->loginname, "logged in");
				}
				return true;
				
			} else {
				//Display the login manager if the auth failed
				$this->cms_displayLoginManager();
				if (isset($_POST) && !empty($_POST)) echo "Bad user name or password!<br /><br />";
				
				logChange($this->_CONN, "user", 'log_in',null, clean($this->_CONN,$_POST['login_username']), "FAILED LOGIN");
				
				return false;
			}
			
			 
		} else if($this->cms_getNumUsers() == 0) {
			//Display the user management form
			echo $this->cms_displayUserManager();
		
			//Check again if a user exists after running the user manager
			if($this->cms_getNumUsers() == 0) {
				echo "<p><strong>Hello</strong> there! I see that you have no users setup.<br />
					Use the above form to create a user account to get started!<br />
					Once you have created your user, you will be sent to the login form. Use your new account to access all the awesomeness!</p><br />";
			}
			
			return false;
		} else {
			//Display the login manager if there is no login data posted or no token
			$this->cms_displayLoginManager();
			return false;
		}
	}
	
	/**
	 * Display the login page
	 *
	 */
	public function cms_displayLoginManager() {
		//Display da ferret!
		$this->cms_displayFerret();
	
		//Also the login stuff is important too...
		echo "<div class='cms_loginManager'>";
		echo '
			<h1 class="cms_pageTitle">Ferret CMS Login</h1><br />
			<form action="admin.php" method="post">

			<table class="cms_loginTable">
			<tr>
				<td><label for="login_username">Username:</label></td>
				<td><input name="login_username" id="login_username" type="text" maxlength="50" size="15"/></td>
			</tr><tr>
				<td><label for="login_password">Password:</label></td>
				<td><input name="login_password" id="login_password" type="password" maxlength="50" size="15" /></td>
			</tr>
			</table>
			<div class="clear"></div>
			<br />
			<input type="submit" class="updateBtn" value="Login" /><br /><br />
			</form>
		';
		echo "</div>";
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
				No pages found!
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
				No pages found!
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
		echo ($this->_AUTH ? "Welcome <strong>" . $this->_USER->loginname . "</strong><br /><br />" : "");
		echo "
		<h1>Welcome to the FerretCMS!</h1><br />
		<strong>What is FerretCMS?</strong><br />
		<p class='cms_intro'>
		Glad you asked! FerretCMS is a simple lightweight content management system using PHP and MySQL.<br />
		For updates check out the GitHub repository here:<br />
		<a href='https://github.com/twitch2641/ferretCMS'>https://github.com/twitch2641/ferretCMS</a><br />
		Or the creators account here:<br />
		<a href='https://github.com/twitch2641'>https://github.com/twitch2641</a><br />
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
			</ul>
		
		<br />
		<strong>Cool, anything planned for the future?</strong><br />
		<p class='cms_intro'>
		You bet! Some planned features for FerretCMS are:
		</p>
		<ul class='cms_intro'>
			<li>Plugin management and implementation</li>
			<li>General website settings management</li>
			<li>Template management</li>
			<li>User management</li>
			<li>Content searching</li>
			<li>Change logging</li>
		</ul>
		<br />
		";
		
		echo "<strong>By the way, hows my CMS doing?</strong><br />";
		echo "<p class='cms_intro'>Heres some stats!<br />";
		echo "You have <strong>" . countRecords($this->_CONN, "pages","") . "</strong> page(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"posts","") . "</strong> posts(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"templates","") . "</strong> templates(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"users","") . "</strong> users(s)<br />";
		echo "You have <strong>" . countRecords($this->_CONN,"plugins","") . "</strong> plugins(s)<br />";
		echo "</p>";
	}

	/**
	 * Display the User management
	 *
	 */
	public function cms_displayUserManager() {
	
		//The context is the user ID. We want to update rather than insert if we are editing
		$userId = (isset($_GET['p']) && !empty($_GET['p'])) ? clean($this->_CONN,$_GET['p']) : "new";
		
		$user = new User($this->_CONN);
		
		//Allow access to the user editor if you are authenticated or there are no users
		if($this->_AUTH || $this->cms_getNumUsers() == 0) {
			switch($this->_ACTION) {
				case "update":
					//Determine if the form has been submitted
					if(isset($_POST['saveChanges'])) {
						// User has posted the article edit form: save the new article
						
						$user->storeFormValues($_POST);
						
						if($userId=="new") {
							$result = $user->insert();
							
							//Only display the main form if the user authenticated
							//Since the setup uses the above insert, we want to make sure we don't 
							//genereate the below until they truely login
							if($this->_AUTH && $result) {
								//Re-build the main User after creation
								$this->cms_displayMain();
								logChange($this->_CONN, "user", 'add',$this->_USER->id,$this->_USER->loginname, $user->loginname . " added");
							} else if($result) {
								$this->cms_displayLoginManager();
							} else {
								$user->buildEditForm($userId);
							}
							
						} else {
							$user->update($userId);
							//Re-build the User creation form once we are done
							$user->buildEditForm($userId);
							logChange($this->_CONN,"user", 'update',$this->_USER->id,$this->_USER->loginname, $user->loginname . " updated");
						}
					} else {
						// User has not posted the article edit form yet: display the form
						$user->buildEditForm($userId);
					}
					break;
				case "delete":
					$user->delete($userId);
					$this->cms_displayMain();
					logChange($this->_CONN,"user", 'delete',$this->_USER->id,$this->_USER->loginname, $user->loginname . " deleted");
					break;
				default:
					if($this->cms_getNumUsers() == 0) {
						$user->buildEditForm("new");
					} else {
						echo "Error with user manager<br /><br />";
					}
			}
		} else {
			//Show the login if your not authenticated and users exist in the DB
			$user->buildLogin();
		}
	}
	
	/**
	 * Display the site management page
	 *
	 */
	public function cms_displaySiteManager() {
		
		//The context is the site ID. We want to update rather than insert if we are editing
		$siteId = (isset($_GET['p']) && !empty($_GET['p'])) ? clean($this->_CONN,$_GET['p']) : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the site edit form: save the new article
					$site = new Site($this->_CONN);
					$site->storeFormValues($_POST);
					
					$site->update($siteId);
					//Re-build the site creation form once we are done
					$site->buildEditForm($siteId);
					logChange($this->_CONN, "site", 'update',$this->_USER->id,$this->_USER->loginname, $site->name . " updated");
				
				} else {
					// User has not posted the site edit form yet: display the form
					$site = new Site($this->_CONN);
					$site->buildEditForm($siteId);
				}
				break;
			default:
				echo "Error with site manager<br /><br />";
				$this->cms_displayMain();
		}
	}
	
	
	/**
	 * Display the page management page
	 *
	 */
	public function cms_displayPageManager() {
		
		//The context is the page ID. We want to update rather than insert if we are editing
		$pageId = (isset($_GET['p']) && !empty($_GET['p'])) ? clean($this->_CONN,$_GET['p']) : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$page = new Page($this->_CONN);
					$page->storeFormValues($_POST);
					
					if($pageId=="new") {
						$result = $page->insert();
						if($result) {
							//Re-build the main page after creation
							$this->cms_displayMain();
							logChange($this->_CONN, "page", 'add',$this->_USER->id,$this->_USER->loginname, $page->title . " added");
						} else {
							//Re-build the page creation form since the submission failed
							$page->buildEditForm($pageId);
						}
					} else {
						$page->update($pageId);
						//Re-build the page creation form once we are done
						$page->buildEditForm($pageId);
						logChange($this->_CONN, "page", 'update',$this->_USER->id,$this->_USER->loginname, $page->title . " updated");
					}
				} else {
					// User has not posted the article edit form yet: display the form
					$page = new Page($this->_CONN);
					$page->buildEditForm($pageId);
				}
				break;
			case "delete":
				$page = new Page($this->_CONN);
				$page->delete($pageId);
				$this->cms_displayMain();
				logChange($this->_CONN, "page", 'delete',$this->_USER->id,$this->_USER->loginname, $page->title . " deleted");
				break;
			default:
				echo "Error with page manager<br /><br />";
				$this->cms_displayMain();
		}
		
		
		
	
	}
	
	/**
	 * Display the template management page
	 *
	 */
	public function cms_displayTemplateManager() {
		
		//The context is the page ID. We want to update rather than insert if we are editing
		$templateId = (isset($_GET['p']) && !empty($_GET['p'])) ? clean($this->_CONN,$_GET['p']) : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$template = new Template($this->_CONN);
					$template->storeFormValues($_POST);
					
					if($templateId=="new") {
						$template->insert();
						//Re-build the main page after creation
						$this->cms_displayMain();
						logChange($this->_CONN, "template", 'add',$this->_USER->id,$this->_USER->loginname, $template->name . " added");
					} else {
						$template->update($templateId);
						//Re-build the page creation form once we are done
						$template->buildEditForm($templateId);
						logChange($this->_CONN, "template", 'update',$this->_USER->id,$this->_USER->loginname, $template->name . " updated");
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$template = new Template($this->_CONN);
					$template->buildEditForm($templateId);
				}
				break;
			case "delete":
				$template = new Template($this->_CONN);
				$template->delete($templateId);
				$this->cms_displayMain();
				logChange($this->_CONN, "template", 'delete',$this->_USER->id,$this->_USER->loginname, $template->name . " deleted");
				break;
			default:
				echo "Error with template manager<br /><br />";
				$this->cms_displayMain();
		}
		
		
		
	
	}
	
	/**
	 * Display the plugin management page/ Work In Progress
	 *
	 */
	public function display_pluginManager() {
		
		//The context is the page ID. We want to update rather than insert if we are editing
		$templateId = (isset($_GET['p']) && !empty($_GET['p'])) ? clean($this->_CONN,$_GET['p']) : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$template = new Template($this->_CONN);
					$template->storeFormValues($_POST);
					
					if($templateId=="new") {
						$template->insert();
						//Re-build the main page after creation
						$this->cms_displayMain();
						logChange($this->_CONN, "plugin", 'add',$this->_USER->id,$this->_USER->loginname, $template->name . " added");
					} else {
						$template->update($templateId);
						//Re-build the page creation form once we are done
						$template->buildEditForm($templateId);
						logChange($this->_CONN, "plugin", 'update',$this->_USER->id,$this->_USER->loginname, $template->name . " added");
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$template = new Template($this->_CONN);
					$template->buildEditForm($templateId);
				}
				break;
			case "delete":
				$template = new Template($this->_CONN);
				$template->delete($templateId);
				$this->cms_displayMain();
				logChange($this->_CONN, "plugin", 'delete',$this->_USER->id,$this->_USER->loginname, $template->name . " added");
				break;
			default:
				echo "Error with template manager<br /><br />";
				$this->cms_displayMain();
		}
	}
	
	/**
	 * Display the post management page
	 *
	 */
	public function cms_displayPostManager() {
		//The context is the page ID. We want to update rather than insert if we are editing
		$pageId = isset($_GET['p']) ? clean($this->_CONN,$_GET['p']) : "new";
		$postId = isset($_GET['c']) ? clean($this->_CONN,$_GET['c']) : "new";
		
		switch($this->_ACTION) {
			case "update":
				if(isset($_POST['saveChanges'])) {

					// User has posted the article edit form: save the new article
					$post = new Post($this->_CONN);
					$post->storeFormValues($_POST);
					
					if($postId=="new") {
						$post->insert($pageId);
						logChange($this->_CONN, "post", 'add',$this->_USER->id,$this->_USER->loginname, $post->title . " added");
					}
					else {
						$post->update($postId);
						logChange($this->_CONN, "post", 'update',$this->_USER->id,$this->_USER->loginname, $post->title . " updated");
					}
						
					//Re-build the post creation form once we are done
					$post->buildEditForm($pageId,$postId);
				} else {
					// User has not posted the article edit form yet: display the form
					$post = new Post($this->_CONN);
					$post->buildEditForm($pageId,$postId);
				}
				break;
			case "delete":
				//Delete the post
				$post = new Post($this->_CONN);
				$post->delete($pageId, $postId);
				logChange($this->_CONN, "post", 'delete',$this->_USER->id,$this->_USER->loginname, $post->title . " deleted");
				
				//Display the page form
				$page = new Page($this->_CONN);
				$page->buildEditForm($pageId);
				
				break;
			default:
				echo "Error with post manager<br /><br />";
				$this->cms_displayMain();
		}
		
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
	}

	/**
	 * Build the tables required by the CMS. They will only build if the table doesn't exist.
	 *
	 */
	private function buildDB() {

		/*Table structure for table `board` */
		$sql = "CREATE TABLE IF NOT EXISTS `board` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `board_postId` int(16) DEFAULT NULL,
		  `board_authorId` int(16) DEFAULT NULL,
		  `board_comment` text,
		  `board_replyTo` int(16) DEFAULT NULL,
		  `board_datePosted` datetime DEFAULT NULL,
		  `board_lastUpdated` datetime DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"board\"");
		
		/*Table structure for table `pages` */

		$sql = "CREATE TABLE IF NOT EXISTS `pages` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `page_template` int(16) DEFAULT NULL,
		  `page_safeLink` varchar(32) DEFAULT NULL,
		  `page_meta` text,
		  `page_title` varchar(128) DEFAULT NULL,
		  `page_hasBoard` tinyint(1) DEFAULT NULL,
		  `page_isHome` tinyint(1) DEFAULT NULL,
		  `page_created` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"pages\"");
		
		/*Table structure for table `posts` */

		$sql = "CREATE TABLE IF NOT EXISTS `posts` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `page_id` int(16) DEFAULT NULL,
		  `post_authorId` int(16) DEFAULT NULL,
		  `post_date` datetime DEFAULT NULL,
		  `post_title` varchar(150) DEFAULT NULL,
		  `post_content` text,
		  `post_lastModified` VARCHAR(100) DEFAULT NULL,
		  `post_created` VARCHAR(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"posts\"");
		
		/*Table structure for table `templates` */

		$sql = "CREATE TABLE IF NOT EXISTS `templates` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `template_path` varchar(128) DEFAULT NULL,
		  `template_file` varchar(128) DEFAULT NULL,
		  `template_name` varchar(64) DEFAULT NULL,
		  `template_created` varchar(128) DEFAULT NULL,
		  
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"templates\"");
		
		/*Insert the default template */
		
		$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES";
		$sql .= "('$this->loginname', '$secPass', '$salt', '$this->email','" . time() . "', 1)";
				
		$this->_CONN->query($sql) OR DIE ("Could not insert default template into \"templates\"");
		
		/*Insert site data for `site` if we dont have one already*/
		if(countRecords($this->_CONN, "templates") == 0) {
			$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES('_default', 'Default', 'Default', '" . time() . "')";
			$this->_CONN->query($sql) OR DIE ("Could not insert default data into \"templates\"");
		}
		
		/*Table structure for table `plugins` */

		$sql = "CREATE TABLE IF NOT EXISTS `plugins` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `plugin_path` varchar(128) DEFAULT NULL,
		  `plugin_file` varchar(128) DEFAULT NULL,
		  `tplugin_name` varchar(64) DEFAULT NULL,		  
		  PRIMARY KEY (`id`)
		)";
		
		$this->_CONN->query($sql) OR DIE ("Could not build table \"plugins\"");
		
		
		/*Table structure for table `users` */

		$sql = "CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `user_login` varchar(64) DEFAULT NULL,
		  `user_pass` varchar(64) DEFAULT NULL,
		  `user_salt` varchar(64) DEFAULT NULL,
		  `user_token` varchar(64) DEFAULT NULL,
		  `user_email` varchar(128) DEFAULT NULL,
		  `user_created` varchar(100) DEFAULT NULL,
		  `user_isRegistered` tinyint(1) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"users\"");
		
		/*Table structure for table `log` */

		$sql = "CREATE TABLE IF NOT EXISTS `log` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `log_type` varchar(64) DEFAULT NULL,
		  `log_action` varchar(64) DEFAULT NULL,
		  `log_userId` varchar(64) DEFAULT NULL,
		  `log_user` varchar(64) DEFAULT NULL,
		  `log_info` text,
		  `log_date` datetime DEFAULT NULL,
		  `log_created` varchar(128) DEFAULT NULL,
		  `log_remoteIp` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"log\"");
		
		/*Table structure for table `site` */

		$sql = "CREATE TABLE IF NOT EXISTS `sites` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `site_name` varchar(64) DEFAULT NULL,
		  `site_linkFormat` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->_CONN->query($sql) OR DIE ("Could not build table \"site\"");
		
		
		/*Insert site data for `site` if we dont have one already*/
		if(countRecords($this->_CONN, "sites") == 0) {
			$sql = "INSERT INTO sites (site_name, site_linkFormat) VALUES('My FerretCMS Website', 'clean')";
			$this->_CONN->query($sql) OR DIE ("Could not insert default data into \"site\"");
		}
	}
	
	/**
	 * Display any global warnings such as missing homepage, etc
	 *
	 */
	public function cms_displayWarnings() {
		//Make sure a homepage is set
		$pageSQL = "SELECT * FROM pages WHERE page_isHome=1;";
		$pageResult = $this->_CONN->query($pageSQL);
		
		if (($pageResult == false || mysqli_num_rows($pageResult) == 0) && $this->cms_getNumUsers() != 0 && $this->_AUTH == true)
			echo "<span class='cms_warning'>A homepage is missing! Please set a homepage!</span><br />";
	
	
	}

	/**
	 * By far the MOST important function in the whole CMS
	 *
	 */
	public function cms_displayFerret() {
		echo "<pre class='cms_ferret'>                
                                                                        .`                                           
                                                               ` `..,,,,,,,,``                                       
                                                           ``.,,,:,:;::::,;:,.``````                                 
                                                          ``:::,:,,;;::,::,,.,......```                              
                                                         ..::,,,,,::,:.,,,,;;:,,.......`                             
                                                      ```..,.....,,,,,.,,..,,:::,,..,,,.`                            
                                                 `````.````..```...:.,,....,,;:;;::,:,,..                            
                                               ```.,.````.```.`.,,,,..,..`..`::'';::::,.,                            
                                               `....`````..`.``..,,:.,......:,:';;;:::,,.                            
                                              ```.`.```...``....,::,,,..,...,,:''':;;:,.,`                           
                                              `..`````......,,,.:;:;;:;,,,,,,,;';;:;::,.,`                           
                                              `.```.`..,...:,:::::.:'+''::,,,,:;;;;,;:,..`                           
                                              ````..,,:,,,::;;';'.,:'''+;':,,::;:'';:;:,,`                           
                                              `.``,.,:,:,:::;;'';;:+###'+';::,::':,;:::,,.                           
                                              ```.:.,,,::::::;;';;'#`#+#';;::::,;'+'::::,.                           
                                               ``.;`.,,:,:,,:;;:;''+#@@#'+';::,:;'':,:;:,.                           
                                              ```.#,`.`. `.,.,;::;';;'';++'';;:::;;;;;:;:.                           
                                              ```.+: ``.```,.,,.,;;'''+++;;';:::::::,:;;;,.                          
                                              ``,,;``..,,,,,,...,:;;;;;''''';;;::;;;:,::;;,                          
                                              `.;;.`,::,,,:;:...,,;''+'''''';::;;;::,:;;;';`                         
                                              `.:,`.......,:;.,,::;;'+++'++';;;::;::;;:;;;:`                         
                                               .,. ...:,,::';,,...,,;+++++'';;::;;;::;;;;';,                         
                                               .,.``,.:,,'';:,,,,:;;;++#++'';;::;;;::;;;;';.                         
                                              `.,```,.:::;;:,,,:,,,:;;'++'';;;;;;;;;;;;;;'':`                        
                                               .,```.:''';:,,,,,:::::;'++';;;;;;::;:;;;;''+',`                       
                                              `..````.,::,:::,::;::;::'';;;;;:::;:;;;'''''';,                        
                                               `,````.,:;;:,,,,,:::,;;':;;;:::;;;;::;;'+'''':`                       
                                                ,````.,;:::::,,::::::;;:;:::::::;:;;;''++''':.                       
                                               ``````.:,.,,::;;::,,:::;::;;:;;;;;'';+++++++';;`                      
                                                 .``.,,...,,,:;;;;;:;:;;;:;'';;;;;''++#+++'';;.                      
                                                 ```......,,,:::;;::;;::;;'''';;;''+++##+++''',`                     
                                                   ``.``..,::::::::::::::'''''''''++####++++'':`                     
                                                   `````.,,,:::::::::::::''''+'+'+++#####+++'';,`                    
                                                     ```...,,,,,,,,,,,,:;;'++++++#######+#++''',                     
                                                     `````..,,,,,,,,,::;;''++++##########++++'';,                    
                                                      ...`..,.,,,,,::;;;'+++++#+++######++++++'';`                   
                                                       ,:,.,..,:;';';;+++++#+#++###########+++''':`                  
                                                       .:;;;';'';'''+++++++++++##########+++++++':,                  
                                                       .:;'''''''++++#+++++#+#+############+#++++':`                 
                                                      `.,;''+++''++'++###+################++++++++:`                 
                                                    ```.,;''+'+++++++######+#++###########+++++++';`                 
                                                   `....,:'''+++++++##########+###########+#+#+#+';.                 
                                                  `.,,,.,:;;'++++++++#+#######+###########+++##+++':                 
                                             ``,..,:::,.,,:;'++#+#+++++#######+###############+++++;.                
                                              .,:,;:,:,,,::''++++#++++##+#####++#####@##########+++;`                
                                            .,,,;;;,::,,,::;'+++##+'+++#+######+#+####@#########+++'.                
                                          ..,:,:;;,:::,,,,::''++++++++#+####+##+#################++':`               
                                        ``,:;;::;':::;:,,,,:'''+##++++++++########################++'.               
                                        `,:;;;;';;';:,:,,:;:;'''+++#++++########++###@##@#@########++.`              
                                       `,;;;;;;;;;;::,,,,:::;''''++#++++###+#####+####@###@########++',              
                                     `,::;;;;;;';;:,,,,,,:;;;;'''+++##+#+###+#####+#@#@@#@#@#########+:              
                                    `.,:;''';'';;::::,,,,;;';;;++++#+#+########+#####@##@####@#@#####+:              
                                   .:;'';';;;';;:,,,,,,,;;;';';'+++++###########+######@@@###########+'`             
                                 `.,:;'';''';;;;:,,,,::;;;;:''+++++#####+#+#############@@@@###@######+,             
                                `.:;;'';'';;;;;:,::,:,:;;;'''''++++#######+##@###+######@@@###########+;`            
                               `.,:;''';'';;;;;::,,,,,:;''';'''++++##########@####+##@#@@@@@#@##@######'             
                               `.:;;'''';;;;;;:,,,,,,,:;''+;;'+++#++++########@#####@@@##@@@@@#@#######+:            
                              .,:;;;''''';;::::,,,,,,,:;'''''+'++#+#####@#####@#######@@#@@@@@@########+:            
                            `.,:::;''''';;:::,,,,,,,,::;''''''++######@#@#####@########@@@@@@#@##@#####+,`           
                            `.,:;;;;''';;:;::,,,,,,,,:;;'''+'++++++#####@#######@####@@@@#@@@@#@#@@####+'`           
                             .,:;;;';;;;;;::,,,,,,,.,:;'''''+++#+##############@@#######@@@@#@@@@@#####+'`           
                            `.,::;;;;;;;;;;,,,,,,,,,::;'''+'+++++##########@####@######@@@@@@@@@@#######'`           
                           `..:::;;;;;;:;:::,,,.,,,:::;'+''++#+################@#####@@@@@@@@@@@@@@#####+,           
                           `.,:;;;'';;:::::,:,,,,,,,:;';'+++#+#################@+##@###@@@@@###@#@#@####:.           
                    ``......,,,:::;;;;:;:;::::,,,,::;;''+++++++####+###@########@######@@@#@@@#@@@@@####+`           
               ```.,:':;:,..,,,:::;;;;;::::,,,,.,,,,;''++++#++++#######@##@##+#####@##@@@@@#@##@#@#@@####;.          
              `.::;;;;;:,,.,,,,:;;;;;;::::,,,,,.,,:::''+'+++++####+##@#@##@########@##@@@@@#####@#@######`           
            .:::''';;:::,,,,,,,::;;;;::::,,,,,,,,,::;'+++++++########@#@+##@#@####@@##@@@#@#####@########+:          
           `.::;''';;;;:,,,,:::::::::::::,:,,,,,,,;,:;'++#++####+#+##@@##@@@#######@#@@@@@@######@##@####',          
          .:;;'+'';';;::,,,,,,,::;::::::::,,,,,,,,,::''++#+#######+#@@@##@@#@#@###@@@@@@@@@##@###########+:          
        `,;'+++++''';;;:,:,,,,::;;;;;;::::,,,,,,.,::;''+++##########@@@#@#@#@@@@####@#@@@@@##############+.          
       `,;'++++'++++;;::::::::::;;;;;;::,:,,,,,,,,:;;:;++######@@@##@@@@@@##@#@@@#@#@@@@@@###############+,,         
       .:'+++++#+++'+'';;;::::::;;;;;:::,,,,,,,,,,,::;;'+##########@#@#@#@@###@@@@@@@@@#@#######+########',`         
      `.'++######+'+';;:;;::::::;;;;;;::,,,,,,,,,,,:'';'+#+####@###@@#@#@@@@@@@@@@@@@@@@@@#######+########;`         
      .;+++####++++';;:;;;:::::;::;;:::,,,,,,,..,,,:::;''+#########@@##@@@#@@#@@@@@@@@@@@@+##########@###+;,`        
     `,'++#+###++';,::;;;;;;:;;::;;;;:::,,,,,,,,,,,:::;;'+##########@#@@@@@@@@@@@@@@@@#@@#+@######+#######;`         
     ,;'++#####+:....:;;';':;;:;;;;;::,:,,,,,,,,,,,,:::;++#########@##@@@@@@@@@@@@@#@@@@@+#######++######+'`         
    `,:++####++,`   `.;';';';::;;;;;:::,,,,,,,,,,,::::;''+#############@@@@@@@@@@@@@@@@###################'`         
    `.'+####++,`     ,';'';;:;;;;;;;;:,,,,,,,,,,,,,,::;;'++#####@######@@@@@@@@@@@@@@@@############+#+###;:          
    `,++####++.      ;';';;;';;;;;;;::::,,,,:,,,,,,::::;''+########@###@@#@@@@@@@@@@@@##########+#++++###+:`         
     :'++####:      .''''+;';''';;;;::::,,,,,,,,,:::::;:;''++##########@@@@@@@@@@@@@#@######@###+#+######+:`         
    `;;++####:      :'+''''''+';';:;;::,,:,,,,,,,:,:::::;;'++#####@#####@@@@@@@@@@@############+++++######.          
    .:;++####:     .;++'''''+';'';;::::::,,,,,,,,:,::::::;'++###++@####@@#@@@@#@@@##@####++####+++++##@###',         
    `.:+#####+    `:''+;+++''++';';:::::,:,:,,:,::::::;:;;'+++###+@#@###@@@@####@@#####++'#';##+''++######,          
    `.:'+####+,   ,'++++++'+''';;';;;;:::,::::::::::::;;;;'''+++###@####@@@@#@########++,:':+##+'+'+######;`         
     `,'+###++',``;++++#++''++''+';;:;;:::::::,::::;:;;:;;;;++##+######@@#@@@@##@@###+';...,'#+++++####@@#'          
      .:'++##'';;;+###+##+++##'+';;;';;;:;:::::::::;;;:;;;;'+++#######@@@@@@##@@@@###+;.`` `:#++++++####@@+.`        
      `,;'+#+++'++##@##+####+'+'';';';;;;;:::::::;::;:;;;;;''+++######@@@@@@@@@@@###+:,    .:+++++++####@#+`         
       .:;'++++''+#@@@@#@##+##'''+;;;;;;;;::;;;;;;;::;:;;;;'+++#+####@@@@@@@#@@@####'```   `,+#+++++###@##:`         
        ..'++++'+#++###@@####++++';;,:,;::;;;;;;;;;:;::;:;;''+++#+##@@@@@@@@@@@@###++.      ,#+++++##+##@#:`         
         `,;'+';++;;#;'##'+####+':,.``.:,,;';;;';;:;;::;;;'''+++####@@@@@@@@##@@###+#:     `;;'++####@###@'`         
           `,;;;'+''++###+#@@@#+;:`   ```.:;;'';';;;;;'';''''+++##@@@@@@@@@@@#@######+.    `` ;+####@@@####,         
             .''++++#######@###',.    `  `.,:;''''''';''''''+'+#@#@@@@@@@@@#@#@######;,      .'####@@#####+;.`       
            `;''+###++@@@+#@@+@',`      ` ``.:,:'''+'''+++++++###@@@@##@#+@@########+,`      ,####@@#'+@##''+;`      
          ``.:'''###'#@@#'+##+@#+:`         ```..:;'+++######+##'+###@#@'#########';:.`      ;#@#++@+####+;+#'',`    
         ``..,;:;+#';+###';+'';:,.`       ````...::;;;'''''++'+#++#+:##+'+#;:+#+:'';:.`     `'+#'##+#+;##++#;++;.`   
         ````....,,,,,,,,,,,,,,,..``````````.....,,,,,,,,,,,,:':+##',##+;++'###+#;';:,.``` `;'+#;+###',@+#+#':+',    
          `````````...............````````........,,,,,,,,,,,,;.'++;'###+#++'##++++';,.````.;;'#:####':####+''+;``   
             ```````````..........````````................,,,,;;:;':;####++:;##++'';:,.`````,;'+:'+++;:'+++';;':`    
                 ```````````````````````````.....................,,,,:::,,,,,.,,,.....``````...,,,,,,,,,,,,....`     
                      `````````````````````````````````````....`.```..................``````.....`...`````````       
                                ```````````````````````````````````````````````````````````````````````````          
                                              ``````````````````````````````````````````    ````````                 

		</pre>";
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
	
	//USER VIEW FUNCTIONS ###############################################################################
	
	/**
	 * Loads the page and template for the live website
	 *
	 * @param $pSafeLink		The link used to access the page. Ex: p=blog
	 * 
	 */
	public function load_page($pSafeLink) {
		global $cms; //Make the CMS variable a global so the pages can reference it
	
		$page = new Page($this->_CONN);

		//Load the page
		if(isset($pSafeLink) && $pSafeLink != null && $pSafeLink != "home" && strpos($pSafeLink,"SYS_") === false) {
			$pageSQL = "SELECT * FROM pages WHERE page_safeLink='$pSafeLink'";
			$pageResult = $this->_CONN->query($pageSQL);

			if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 )
				$pageData = mysqli_fetch_assoc($pageResult);

			if(isset($pageData)) {
				$page->loadRecord($pageData['id']);
			}
		} else {
			$page->loadRecord($pSafeLink);
		}
		
		//Load the page
		if(isset($page->template) && $page->template != null && $page->constr == true && strpos($pSafeLink,"SYS_") === false) {
			$templateSQL = "SELECT * FROM templates WHERE id=$page->template";
			$templateResult = $this->_CONN->query($templateSQL);

			if ($templateResult !== false && mysqli_num_rows($templateResult) > 0 )
				$template = mysqli_fetch_assoc($templateResult);

			if(isset($template)) {
				//Load the template file
				$page->templatePath = $template['template_path'];
				require(TEMPLATE_PATH . "/" . $template['template_path'] . "/" . $template['template_file']);
			}
		} else {
			//Check to see if the CMS has already been setup
			if($this->cms_getNumUsers() == 0) {
				echo "<p style='font-family:arial;text-align:center;'><strong>Hello</strong> there! I see that you have no users setup.<br />
				<a href='admin.php'>Click here to redirect to the admin page to setup your CMS.</a>
				</p><br />";
			} else {
				require(loadErrorPage($pSafeLink));
			}
		}
	
	}
	
	/**
	 * The navigation bar to show on the user page
	 *
	 * @param $data		 An array of all the safe links to display in the navigation. Ex home, blog, archive
	 * 
	 */
	public function load_navigation($data=array()) {

		echo "<ul class='cms_ul_nav'>";
		
		for($i=0;$i<count($data);$i++) {
			$pageSQL = "SELECT * FROM pages WHERE page_safelink='$data[$i]'";
			$pageResult = $this->_CONN->query($pageSQL);

			if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 )
				$pageData = mysqli_fetch_assoc($pageResult);

			if(isset($pageData)) {
				echo "<li class='cms_li_nav' id='nav-$data[$i]'><a href='" . formatLink($this->_LINKFORMAT, $pageData['page_safeLink'])  . "'>" . $pageData['page_title'] . "</a></li>";
			}
		}
		
		echo "</ul>";
	}
	
}

?>

