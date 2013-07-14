<?php
include_once('_class/dbFunctions.php');
include_once('_class/user.php');
include_once('_class/page.php');
include_once('_class/post.php');
include_once('_class/template.php');

class cms {

	var $_MODE = "user";
	var $_TYPE = "user";
	var $_ACTION = "user";
	var $_PARENT = "user";
	var $_CHILD = "user";
	var $_USERPAGE = "user";
	
	//Login stuff
	var $_AUTH = false;	
	var $_USER = null;
	var $_USERNAME = null;
	var $_PASSWORD = null;
	
	
	/* This function is called whenever the class is first initialized.
	 * This takes care of page routing
	*/
	public function load ($mode) {
		//Let the CMS know if we are running user or admin rules
		$this->_MODE = $mode;
		
		//Admin Gets
		$this->_TYPE = isset( $_GET['type'] ) ? $_GET['type'] : "";
		$this->_ACTION = isset( $_GET['action'] ) ? $_GET['action'] : "";
		$this->_PARENT = isset( $_GET['p'] ) ? $_GET['p'] : "";
		$this->_CHILD = isset( $_GET['c'] ) ? $_GET['c'] : "";

		//Set the username and password off the cookies
		$_USERNAME = (isset($_COOKIE['username']) ? $_COOKIE['username'] : null);
		$_PASSWORD = (isset($_COOKIE['password']) ? $_COOKIE['password'] : null);
		
		if($mode == "admin")
			$this->_AUTH = $this->cms_authUser($_USERNAME, $_PASSWORD);
		
		//user gets
		$this->_USERPAGE = isset( $_GET['p'] ) ? $_GET['p'] : "home";
		
		$this->display_cmsWarnings();

		if($this->_AUTH) {
			//Build the manager
			switch($this->_TYPE) {
				case "page":
					echo $this->cms_displayPageManager();
					break;
				case "template":
					echo $this->cms_displayTemplateManager();
					break;
				case "post":
					echo $this->cms_displayPostManager();
					break;
				case "user":
					echo $this->cms_displayUserManager();
					break;
				default:
					$this->cms_displayMain();
					break;
			}
		} else if($mode == "user"){
			//User view mode
			$this->load_page($this->_USERPAGE);
		}
	}

	
	/* User to determine if we should show the user create page
	*/
	private function cms_getNumUsers() {
		$userSQL = "SELECT * FROM users;";
		$userResult = mysql_query($userSQL);

		$numUser = mysql_num_rows($userResult);
		return $numUser;
	}
	
	private function cms_authUser($username, $pass) {
		
		if((($username!=null && $pass != null) || (isset($_POST['login_username']) && isset($_POST['login_password']))) && $this->cms_getNumUsers() > 0) {
			if(isset($_POST['login_username']) && isset($_POST['login_password'])) {
				//Hash the password, apply salt, rehash
				$secPass = hash('sha256',($_POST['login_password']));
				$secPass = hash('sha256',($secPass . get_userSalt($_POST['login_username'])));
				
				$userSQL = "SELECT * FROM users WHERE user_login='" . $_POST['login_username'] . "' AND user_pass='$secPass';";
			} else {
				$userSQL = "SELECT * FROM users WHERE user_login='$username' AND user_pass='$pass';";
			}
			
			$userResult = mysql_query($userSQL);

			if ($userResult !== false && mysql_num_rows($userResult) > 0 ) {
				$userData = mysql_fetch_assoc($userResult);

				$user = new User;
				
				//Set the user data
				$user->id = ($userData['id']);
				$user->loginname = ($userData['user_login']);
				$user->password = ($userData['user_pass']);
				$user->salt = ($userData['user_salt']);
				$user->email = ($userData['user_email']);
				$user->isRegistered = ($userData['user_isRegistered']);
	
				//Set the global variable
				$this->_USER = $user;
				
				//30 minute auth timeout
				$timeout = time() + 900; 
				setcookie("username", $user->loginname, $timeout); 
				setcookie("password", $user->password, $timeout); 
				
				return true;
				
			} else {
				$this->cms_displayLoginManager();
				if (isset($_POST) && !empty($_POST)) echo "Bad username or password!<br /><br />";
				return false;
			}
			
			 
		} else if($this->cms_getNumUsers() == 0) {
			echo "<p><strong>Hello</strong> there! I see that you have no users setup.<br />
					Use the below form to create a user account to get started!<br />
					Once you have created your user, you will be sent to the login form. Use your new account to access all the awesomeness!</p><br />";
			
			//Display the user management form
			echo $this->cms_displayUserManager();
			return false;
		} else {
			
			$this->cms_displayLoginManager();
			return false;
		}
	}
	
	public function cms_displayLoginManager() {
		echo "<div id='main_content'>";
		echo '
			<form action="admin.php" method="post">

			<label for="login_username">Username:</label><br />
			<input name="login_username" id="login_username" type="text" maxlength="50" size="15"/>
			<div class="clear"></div>

			<label for="login_password">Password:</label><br />
			<input name="login_password" id="login_password" type="password" maxlength="50" size="15" />
			<div class="clear"></div>
			<br />

			<div class="clear"></div>
			<br />
			<input type="submit" class="updateBtn" value="Login" /><br /><br />
			</form>
		';
		echo "</div>";

		echo "<div class='clear'></div>";
	
	}
	
		/* Display the User management
	*/
	public function cms_displayUserManager() {
		
		//The context is the user ID. We want to update rather than insert if we are editing
		$userId = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : "new";
		
		$user = new User;
		
		//Allow access to the user editor if you are authenticated or there are no users
		if($this->_AUTH || $this->cms_getNumUsers() == 0) {
			switch($this->_ACTION) {
				case "update":
					//Determine if the form has been submitted
					if(isset($_POST['saveChanges'])) {
						// User has posted the article edit form: save the new article
						
						$user->storeFormValues($_POST);
						
						if($userId=="new") {
							$user->insert();
							
							//Only display the main form if the user authenticated
							//Since the setup uses the above insert, we want to make sure we don't 
							//genereate the below until they truely login
							if($this->_AUTH) {
								//Re-build the main User after creation
								$this->cms_displayMain();
							} else {
								$this->cms_displayLoginManager();
							}
						} else {
							$user->update($userId);
							//Re-build the User creation form once we are done
							$user->buildEditForm($userId);
						}
					} else {
						// User has not posted the article edit form yet: display the form
						$user->buildEditForm($userId);
					}
					break;
				case "delete":
					$user->delete($userId);
					$this->cms_displayMain();
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
	
	/* Display the list of all pages
	*/
	public function cms_displayAdminPages() {
		$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
		$pageResult = mysql_query($pageSQL);
	
		if ($pageResult !== false && mysql_num_rows($pageResult) > 0 ) {
			while($row = mysql_fetch_assoc($pageResult) ) {
				
				$title = stripslashes($row['page_title']);
				$safeLink = stripslashes($row['page_safeLink']);

				echo "
				<div class=\"page\">
					
					
					<h2>
					<a href=\"admin.php?type=page&action=update&p=".$row['id']."\" " . ($row['page_isHome']==1 ? "id='homepageMarker'":"") . " title='" . ($row['page_isHome']==1 ? "Edit / Manage the homepage":"Edit / Manage this page") . "' class=\"pageEditLink\" >$title</a>
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
	
	/* Display the list of all templates
	*/
	public function cms_displayAdminTemplates() {
		$templateSQL = "SELECT * FROM templates ORDER BY template_created DESC";
		$templateResult = mysql_query($templateSQL);
	
		if ($templateResult !== false && mysql_num_rows($templateResult) > 0 ) {
			while($row = mysql_fetch_assoc($templateResult) ) {
				
				$name = stripslashes($row['template_name']);
				$file = stripslashes($row['template_file']);
				$path = stripslashes($row['template_path']);
				
				echo "
				<div class=\"template\">
					<h2>
					<a href=\"admin.php?type=template&action=update&p=".$row['id']."\" title=\"Edit / Manage this template\" alt=\"Edit / Manage this template\" class=\"pageEditLink\" >$name</a>
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
	
	/* Display the list of all users
	*/
	public function cms_displayAdminUsers() {
		$userSQL = "SELECT * FROM users ORDER BY user_created DESC";
		$userResult = mysql_query($userSQL);
	
		if ($userResult !== false && mysql_num_rows($userResult) > 0 ) {
			while($row = mysql_fetch_assoc($userResult) ) {
				
				$username = stripslashes($row['user_login']);
				$email = stripslashes($row['user_email']);
				
				echo "
				<div class=\"user\">
					<h2>
					<a href=\"admin.php?type=user&action=update&p=".$row['id']."\" title=\"Edit / Manage this user\" alt=\"Edit / Manage this user\" class=\"pageEditLink\" >$username</a>
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
	
	
	
	/* Display the admin homepage.
	 * Currently this is a list of all pages.
	*/
	public function cms_displayMain() {
		
		echo ($this->_AUTH ? "Welcome <strong>" . $this->_USER->loginname . "</strong><br /><br />" : "");
		
		//Build the pages section ##################################################################################
		echo "<div id='main_content'>";
		
		echo "<h2>Pages</h2><br /><br />";
		$this->cms_displayAdminPages();
		
		
		echo "<br /><br /><hr /><br /><br />
			<h2>Templates</h2><br />";
		$this->cms_displayAdminTemplates();
		
		echo "<br /><br /><hr /><br /><br />
			<h2>Users</h2><br />";
		$this->cms_displayAdminUsers();
		
		
		echo "<br /><br /></div>";
		
		//Build the admin tools section ################################################################################
		
		echo "
		<div id='main_tools'><p class=\"admin_link\">
			<h2>Admin Actions</h2>
			<br /><br />
			<a href=\"{$_SERVER['PHP_SELF']}?type=page&action=update\" class=\"actionLink\">Add a New Page</a><br />
			<a href=\"{$_SERVER['PHP_SELF']}?type=template&action=update\" class=\"actionLink\">Add a New Template</a><br />
			<a href=\"{$_SERVER['PHP_SELF']}?type=user&action=update\" class=\"actionLink\">Add a New User</a><br />
			<br /><br />
			</p>
		</div><div class='clear'></div>
		";

	}

	/* Display the page management page
	*/
	public function cms_displayPageManager() {
		
		//The context is the page ID. We want to update rather than insert if we are editing
		$pageId = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$page = new Page;
					$page->storeFormValues($_POST);
					
					if($pageId=="new") {
						$page->insert();
						//Re-build the main page after creation
						$this->cms_displayMain();
					} else {
						$page->update($pageId);
						//Re-build the page creation form once we are done
						$page->buildEditForm($pageId);
					}
				} else {
					// User has not posted the article edit form yet: display the form
					$page = new Page;
					$page->buildEditForm($pageId);
				}
				break;
			case "delete":
				$page = new Page;
				$page->delete($pageId);
				$this->cms_displayMain();
				break;
			default:
				echo "Error with page manager<br /><br />";
				$this->cms_displayMain();
		}
		
		
		
	
	}
	
	/* Display the template management page
	*/
	public function cms_displayTemplateManager() {
		
		//The context is the page ID. We want to update rather than insert if we are editing
		$templateId = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$template = new Template;
					$template->storeFormValues($_POST);
					
					if($templateId=="new") {
						$template->insert();
						//Re-build the main page after creation
						$this->cms_displayMain();
					} else {
						$template->update($templateId);
						//Re-build the page creation form once we are done
						$template->buildEditForm($templateId);
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$template = new Template;
					$template->buildEditForm($templateId);
				}
				break;
			case "delete":
				$template = new Template;
				$template->delete($templateId);
				$this->cms_displayMain();
				break;
			default:
				echo "Error with template manager<br /><br />";
				$this->cms_displayMain();
		}
		
		
		
	
	}
	
	
	/* Display the plugin management page
	*/
	public function display_pluginManager() {
		
		//The context is the page ID. We want to update rather than insert if we are editing
		$templateId = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : "new";
		
		switch($this->_ACTION) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$plugin = new Plugin;
					$template->storeFormValues($_POST);
					
					if($templateId=="new") {
						$template->insert();
						//Re-build the main page after creation
						$this->cms_displayMain();
					} else {
						$template->update($templateId);
						//Re-build the page creation form once we are done
						$template->buildEditForm($templateId);
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$template = new Template;
					$template->buildEditForm($templateId);
				}
				break;
			case "delete":
				$template = new Template;
				$template->delete($templateId);
				$this->cms_displayMain();
				break;
			default:
				echo "Error with template manager<br /><br />";
				$this->cms_displayMain();
		}
	}
	
	
	/* Display the post management page
	*/
	public function cms_displayPostManager() {
		//The context is the page ID. We want to update rather than insert if we are editing
		$pageId = isset($_GET['p']) ? $_GET['p'] : "new";
		$postId = isset($_GET['c']) ? $_GET['c'] : "new";
		
		switch($this->_ACTION) {
			case "update":
				if(isset($_POST['saveChanges'])) {

					// User has posted the article edit form: save the new article
					$post = new Post;
					$post->storeFormValues($_POST);
					
					if($postId=="new")
						$post->insert($pageId);
					else
						$post->update($postId);
						
					//Re-build the post creation form once we are done
					$post->buildEditForm($pageId,$postId);
				} else {
					// User has not posted the article edit form yet: display the form
					$post = new Post;
					$post->buildEditForm($pageId,$postId);
				}
				break;
			case "delete":
				//Delete the post
				$post = new Post;
				$post->delete($pageId, $postId);

				//Display the page form
				$page = new Page;
				$page->buildEditForm($pageId);
				
				break;
			default:
				echo "Error with post manager<br /><br />";
				$this->cms_displayMain();
		}
		
	}

	/* Connect to the database defined in config.php
	*/
	public function connect() {
		mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysql_error());
		
		//Create the database if it doesn't exist
		$dbCreate = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`;";
		mysql_query($dbCreate) OR DIE ("Could not build table \"board\"");
		
		//Connect to our shiney new database
		$dbConn = mysql_select_db(DB_NAME) or die("Could not select database. " . mysql_error());

		return $this->buildDB();
	}

	/* Build the tables required by the CMS.
	 * These inserts are NOT going to override or remove.
	 * They will only build if the table doesn't exist.
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
		mysql_query($sql) OR DIE ("Could not build table \"board\"");
		
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
		mysql_query($sql) OR DIE ("Could not build table \"pages\"");
		
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
		mysql_query($sql) OR DIE ("Could not build table \"posts\"");
		
		/*Table structure for table `templates` */

		$sql = "CREATE TABLE IF NOT EXISTS `templates` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `template_path` varchar(128) DEFAULT NULL,
		  `template_file` varchar(128) DEFAULT NULL,
		  `template_name` varchar(64) DEFAULT NULL,
		  `template_created` varchar(128) DEFAULT NULL,
		  
		  PRIMARY KEY (`id`)
		)";
		mysql_query($sql) OR DIE ("Could not build table \"templates\"");
		
		
		/*Table structure for table `plugins` */

		$sql = "CREATE TABLE IF NOT EXISTS `plugins` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `plugin_path` varchar(128) DEFAULT NULL,
		  `plugin_file` varchar(128) DEFAULT NULL,
		  `tplugin_name` varchar(64) DEFAULT NULL,		  
		  PRIMARY KEY (`id`)
		)";
		
		mysql_query($sql) OR DIE ("Could not build table \"plugins\"");
		
		
		/*Table structure for table `users` */

		$sql = "CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `user_login` varchar(64) DEFAULT NULL,
		  `user_pass` varchar(64) DEFAULT NULL,
		  `user_salt` varchar(64) DEFAULT NULL,
		  `user_email` varchar(128) DEFAULT NULL,
		  `user_created` varchar(100) DEFAULT NULL,
		  `user_isRegistered` tinyint(1) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		mysql_query($sql) OR DIE ("Could not build table \"users\"");
		
	}
	
	public function display_cmsWarnings() {
		//Make sure a homepage is set
		$pageSQL = "SELECT * FROM pages WHERE page_isHome=1;";
		$pageResult = mysql_query($pageSQL);

		if ($pageResult == false || mysql_num_rows($pageResult) == 0 )
			echo "<span class='cms_warning'>A homepage is missing! Please set a homepage!</span><br />";
	
	
	}

	//USER VIEW FUNCTIONS ###############################################################################
	
	public function load_page($pSafeLink) {
		//make the CMS functions available on the page
		global $cms;
		$page = new Page;
		
		//Load the page
		if(isset($pSafeLink) && $pSafeLink != null && $pSafeLink != "home") {
			$pageSQL = "SELECT * FROM pages WHERE page_safeLink='$pSafeLink'";
			$pageResult = mysql_query($pageSQL);

			if ($pageResult !== false && mysql_num_rows($pageResult) > 0 )
				$pageData = mysql_fetch_assoc($pageResult);

			if(isset($pageData)) {
				$page->loadRecord($pageData['id']);
			}
		} else {
			$page->loadRecord($pSafeLink);
		}
		
		//Load the page
		if(isset($page->template) && $page->template != null && $page->constr == true) {
			$templateSQL = "SELECT * FROM templates WHERE id=$page->template";
			$templateResult = mysql_query($templateSQL);

			if ($templateResult !== false && mysql_num_rows($templateResult) > 0 )
				$template = mysql_fetch_assoc($templateResult);

			if(isset($template)) {
				//Load the template file
				$page->templatePath = $template['template_path'];
				require(TEMPLATE_PATH . "/" . $template['template_path'] . "/" . $template['template_file']);
			}
		} else {require(PAGE_NOTFOUND);}
	
	}
	
	public function load_navigation($data=array()) {
		
		echo "<ul class='cms_ul_nav'>";
		
		for($i=0;$i<count($data);$i++) {
			$pageSQL = "SELECT * FROM pages WHERE page_safelink='$data[$i]'";
			$pageResult = mysql_query($pageSQL);

			if ($pageResult !== false && mysql_num_rows($pageResult) > 0 )
				$pageData = mysql_fetch_assoc($pageResult);

			if(isset($pageData)) {
				echo "<li class='cms_li_nav'><a href='?p=" . $pageData['page_safeLink'] . "'>" . $pageData['page_title'] . "</a></li>";
			}
		
		}
		
		echo "</ul>";
	}
	
}

?>