<?php

/**
 * Class to handle user accounts
 *
 * @author Jacob Rogaishio
 * 
 */
class account extends model
{
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $loginname = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"loginname");
	protected $password = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"password");
	protected $token = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"token");
	protected $salt = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"salt");
	protected $email = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"email");
	protected $isRegistered = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"isRegistered");
	protected $groupId = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"groupId");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	//Non-persistant properties
	protected $password2 = null;
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params=array()) {
		// Store all the parameters. phpORM uses PDO parameter strings to handle injection
		if(isset($params['username'])) $this->setLoginname($params['username']);
		if(isset($params['password'])) $this->setPassword($params['password']);
		if(isset($params['password2'])) $this->password2 = $params['password2'];
		if(isset($params['email'])) $this->setEmail($params['email']);
		if(isset($params['groupId'])) $this->setGroupId($params['groupId']);
		
		//Reset the users salt and build the password
		$salt = unique_salt();
		$secPass = hash('sha256',$this->getPassword());
		$secPass = hash('sha256',($secPass . $salt));
		$this->setPassword($secPass);
		$this->password2 = hash('sha256',(hash('sha256',$this->password2) . $salt));
		$this->setSalt($salt);
	}

	/**
	 * validate the fields
	 * 
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";

		if($this->getLoginname() == "") {
			$ret = "Please enter a username.";
		} else if($this->getPassword() == "") {
			$ret = "Please enter a password.";
		} else if($this->getPassword() != $this->password2) {
			$ret = "The passwords don't match.";
		} else if($this->getEmail() == "") {
			$ret = "Please enter an email.";
		} else if(!filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) {
			$ret = "Email address is not valid.";
		}
		
		return $ret;
	}
	
	/**
	 * Loads the user object members based off the user id in the database
	 * 
	 * @param $userId	The user to be loaded
	 */
	public function loadRecord($p=null, $c=null) {
		if(isset($p) && $p != null) {
			$userResult = $this->load($p);
						
			//Set a field to use by the logger
			$this->logField = $this->getLoginname();
		}
	}
	
	/**
	 * Builds the admin editor form to add / update users
	 * 
	 * @param $userId	The user to be edited
	 */
	public function buildEditForm($userId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($userId);
		if($userId != null)
			echo '<a href="admin.php">Home</a> > <a href="admin.php?type=account&action=read">User List</a> > <a href="admin.php?type=account&action=update&p=' . $userId . '">User</a><br /><br />';

		echo '
			<form action="admin.php?type=account&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $this->getId() . '" method="post">

			<label for="username">Username:</label><br />
			<input name="username" id="username" class="cms_username"type="text" maxlength="150" value="' . $this->getLoginname() . '" ' . ($this->getLoginname() != null ? "readonly=readonly" : "") . ' />
			<div class="clear"></div>
			<br />
					
			<label for="password">Password:</label><br />
			<input name="password" id="password" type="password" maxlength="150" value="" />
			<div class="clear"></div>
			<br />
					
			<label for="password2">Repeat Password:</label><br />
			<input name="password2" id="password2" type="password" maxlength="150" value="" />
			<div class="clear"></div>
			<br />		
					
			<label for="email">Email Address:</label><br />
			<input name="email" id="email" type="text" maxlength="150" value="' . $this->getEmail() . '" />
			<div class="clear"></div>
											
			<label for="groupId">Permission Group:</label><br />';
			echo getFormattedGroups($this->conn, "dropdown", "groupId", $this->getGroupId());
			echo '
			<div class="clear"></div>
			<br />

				
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($userId) || $userId == null) ? "Create" : "Update") . ' This User!" /><br /><br />
			' . ((isset($userId) && $userId != null) ? '<a href="admin.php?type=account&action=delete&p=' . $this->getId() . '"" class="deleteBtn">Delete This User!</a><br /><br />' : '') . '
			</form>
		';
	}
	
	/**
	 * Display the user management page
	 * 
	 * @param $action	The action to be performed such as update or delete
	 * @param $parent	The ID of the user object to be edited. This is the p GET Data
	 * @param $child	This is the c GET Data
	 * @param $user		The user making the change
	 * @param $auth		A boolean value depending on if the user is logged in
	 * 
	 * @return Returns true on change success otherwise false
	 *
	 */
	public function displayManager($action, $parent, $child, $user, $auth=null) {
		$this->loadRecord($parent);
		$ret = false;
		//Allow access to the user editor if you are authenticated or there are no users
		if($auth || countRecords($this->conn,$this->table) == 0) {
			switch($action) {
				case "read":
					if($user->checkPermission($this->table, 'read')) {
						$this->displayModelList();
					} else {
						echo "You do not have permissions to '<strong>read</strong>' records for " . $this->table . ".<br />";
					}
					break;
				case "insert":
					if(countRecords($this->conn,$this->table) == 0 || $user->checkPermission($this->table, 'insert')) {
						//Determine if the form has been submitted
						if(isset($_POST['saveChanges'])) {
							// User has posted the article edit form: save the new article
								
							$this->storeFormValues($_POST);
								
							if($parent == null) {
								$result = $this->insert();
									
								//Only display the main form if the user authenticated
								//Since the setup uses the above insert, we want to make sure we don't
								//genereate the below until they truely login
								if(!$result) {
									$this->buildEditForm($parent);
								} else if($auth) {
									//Re-build the main User after creation
									$ret = true;
									$this->log->trackChange($this->table, 'add',$this->getId(),$this->getLoginname(), $this->getLoginname() . " added");
								} else {
									parent::render("siteLogin");
								}
							}
						} else {
							// User has not posted the template edit form yet: display the form
							$this->buildEditForm($parent);
						}
					} else {
						echo "You do not have permissions to '<strong>insert</strong>' records for " . $this->table . ".<br />";
					}
					break;
				case "update":
					if($user->checkPermission($this->table, 'update')) {
						//Determine if the form has been submitted
						if(isset($_POST['saveChanges'])) {
							// User has posted the article edit form: save the new article
							$this->storeFormValues($_POST);
		
							$result = $this->update($parent);
								
							if(!$result) {
								$this->buildEditForm($parent);
							} else {
								//Re-build the User creation form once we are done
								$this->buildEditForm($parent);
								$this->log->trackChange($this->table, 'update',$user->getId(),$user->getLoginname(), $this->getLoginname() . " updated");
							}
							
						} else {
							// User has not posted the article edit form yet: display the form
							$this->buildEditForm($parent);
						}
					} else {
						echo "You do not have permissions to '<strong>update</strong>' records for " . $this->table . ".<br />";
					}
					break;
				case "delete":
					if($user->checkPermission($this->table, 'delete')) {
						$this->delete($parent);
						$ret = true;
						$this->log->trackChange($this->table, 'delete',$user->getId(),$user->getLoginname(), $this->getLoginname() . " deleted");
					} else {
						echo "You do not have permissions to '<strong>delete</strong>' records for " . $this->table . ".<br />";
					}
					break;
				default:
					if(countRecords($this->conn,$this->table) == 0) {
						$this->buildEditForm(null);
					} else {
						echo "Error with account manager<br /><br />";
					}
			}
		} else {
			//Show the login if your not authenticated and users exist in the DB
			echo "Authentication Error!";
		}
		return $ret;
	}
	
	/**
	 * Display the list of all accounts
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=account&action=read">Account List</a><br /><br />';
	
		$accountList = $this->loadArr(new account($this->conn, $this->log), "created:DESC");
		
		if (count($accountList)) {
			foreach($accountList as $account) {		
				echo "
				<div class=\"user\">
					<h2>
					<a href=\"admin.php?type=account&action=update&p=".$account->getId()."\" title=\"Edit / Manage this user\" alt=\"Edit / Manage this user\" class=\"cms_pageEditLink\" >" . $account->getLoginname() . "</a>
					</h2>
					<p>" . lookupGroupNameById($this->conn, $account->getGroupId()) . " Group<br />" . $account->getEmail() . "<br /><br /></p>
				</div>";
			}
		} else {
			echo "<p>No users found!</p>";
		}
	}
	
	/**
	 * Checks the users permission to see if they can access an object
	 * 
	 * @param $model	The object to be accessed
	 * @param $change	The action to be performed
	 * 
	 * @return Returns true if you have access, otherwise false
	 */
	public function checkPermission($model, $change) {
		$ret = false;
		$permissions = getRecords($this->conn, "permission", array("*"), "model='$model' AND groupId=" . $this->getGroupId(), $order=null);
		if(is_array($permissions)) {
			switch($change) {
				case "read":
					$ret = ($permissions['readAction'] == 1 ? true : false);
					break;
				case "insert":
					$ret = ($permissions['insertAction'] == 1 ? true : false);
					break;
				case "update":
					$ret = ($permissions['updateAction'] == 1 ? true : false);
					break;
				case "delete":
					$ret = ($permissions['deleteAction'] == 1 ? true : false);
					break;
			}
		}
		
		
		return $ret;
	}
	
}

?>
