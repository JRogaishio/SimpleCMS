<?php

/**
 * Class to handle users
 *
 * @author Jacob Rogaishio
 * 
 */
class account extends model
{
	// Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $loginname = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"loginname");
	protected $password = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"password");
	protected $password2 = null;
	protected $token = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"token");
	protected $salt = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"salt");
	protected $email = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"email");
	protected $isRegistered = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"isRegistered");
	protected $groupId = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"groupId");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params=array()) {
		// Store all the parameters
		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['username'])) $this->setLoginname(clean($this->conn, $params['username']));
		if(isset($params['password'])) $this->setPassword(clean($this->conn, $params['password']));
		if(isset($params['password2'])) $this->password2 = clean($this->conn, $params['password2']);
		if(isset($params['email'])) $this->setEmail(clean($this->conn, $params['email']));
		if(isset($params['groupId'])) $this->setGroupId(clean($this->conn, $params['groupId']));

		$this->constr = true;
	}

	/**
	 * validate the fields
	 * 
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
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
	 * Inserts the current user object into the database
	 * 
	 * @return Returns true on insert success
	 */
	public function insert() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$salt = unique_salt();
				$secPass = hash('sha256',$this->getPassword());
				$secPass = hash('sha256',($secPass . $salt));
				$this->setPassword($secPass);
				$this->setSalt($salt);
				$this->setCreated(time());
				$result = $this->save();
				
				if($result) {
					echo "<span class='update_notice'>Created user successfully!</span><br /><br />";
				} else {
					$ret = false;
				}
			} else {
				$ret = false;
				echo "<p class='cms_warning'>" . $error . "</p><br />";
			}

		} else {
			$ret = false;
			echo "Failed to load form data!";
		}
		return $ret;
	}

	/**
	 * Updates the current user object in the database.
	 * 
	 * @param $userId	The user Id to update
	 * 
	 * @return returns true if the update was successful
	 */
	public function update() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$secPass = hash('sha256',$this->password);
				$secPass = hash('sha256',($secPass . get_userSalt($this->conn, $this->loginname)));
				$this->setPassword($secPass);
				$this->save();
				/*
				$sql = "UPDATE user SET
				account_login = '$this->loginname', 
				account_pass = '$secPass', 
				account_email = '$this->email'
				WHERE id=" . $this->id . ";";
	
				$result = $this->conn->query($sql) OR DIE ("Could not update user!");*/
				if($result) {
					echo "<span class='update_notice'>Updated user successfully!</span><br /><br />";
				} else {
					$ret = false;
				}
			} else {
				$ret = false;
				echo "<div class='cms_warning'>" . $error . "</div>";
			}
		} else {
			$ret = false;
			echo "Failed to load form data!";
		}
		return $ret;
	}

	/**
	 * Deletes the current user object from the database.
	 * 
	 * @param $userId	The user to be deleted
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete() {
		echo "<span class='update_notice'>User deleted! Bye bye '$this->loginname', we will miss you.</span><br /><br />";
		$this->delete();
		//$userSQL = "DELETE FROM " . $this->table . " WHERE id=" . $this->id;
		//$userResult = $this->conn->query($userSQL);
		
		return $userResult;
	}
	
	/**
	 * Loads the user object members based off the user id in the database
	 * 
	 * @param $userId	The user to be loaded
	 */
	public function loadRecord($userId) {
		//Set a field to use by the logger
		$this->logField = $this->getLoginname();
		
		if(isset($userId) && $userId != null) {
			
			$userSQL = "SELECT * FROM " . $this->table . " WHERE id=$userId";
				
			//$userResult = $this->conn->query($userSQL);
			$userResult = $this->load($userId);
						
			$this->constr = true;
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
								$this->log->trackChange($this->table, 'update',$user->getId(),$user->getLoginname(), $this->loginname . " updated");
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
						$this->log->trackChange($this->table, 'delete',$user->getId(),$user->getLoginname(), $this->loginname . " deleted");
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
	
		$accountSQL = "SELECT * FROM " . $this->table . " ORDER BY account_created DESC";
		$accountResult = $this->conn->query($accountSQL);
	
		if ($accountResult !== false && mysqli_num_rows($accountResult) > 0 ) {
			while($row = mysqli_fetch_assoc($accountResult) ) {
	
				$username = stripslashes($row['account_login']);
				$email = stripslashes($row['account_email']);
	
				echo "
				<div class=\"user\">
					<h2>
					<a href=\"admin.php?type=account&action=update&p=".$row['id']."\" title=\"Edit / Manage this user\" alt=\"Edit / Manage this user\" class=\"cms_pageEditLink\" >$username</a>
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
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		$this->persist();
		
		/*Table structure for table `users` */
		/*$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `account_login` varchar(64) DEFAULT NULL,
		  `account_pass` varchar(64) DEFAULT NULL,
		  `account_salt` varchar(64) DEFAULT NULL,
		  `account_token` varchar(64) DEFAULT NULL,
		  `account_email` varchar(128) DEFAULT NULL,
		  `account_created` varchar(100) DEFAULT NULL,
		  `account_isRegistered` tinyint(1) DEFAULT NULL,
		  `account_groupId` int(16) DEFAULT NULL,
				
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");*/
	
	}
	
	public function checkPermission($model, $change) {
		$ret = false;
		$permissions = getRecords($this->conn, "permission", array("*"), "model='$model' AND groupId=" . $this->getGroupId(), $order=null);

		if($permissions != false) {
			$data = mysqli_fetch_assoc($permissions);

			switch($change) {
				case "read":
					$ret = ($data['readAction'] == 1 ? true : false);
					break;
				case "insert":
					$ret = ($data['insertAction'] == 1 ? true : false);
					break;
				case "update":
					$ret = ($data['updateAction'] == 1 ? true : false);
					break;
				case "delete":
					$ret = ($data['deleteAction'] == 1 ? true : false);
					break;
			}
		}
		
		return $ret;
	}
	
}

?>
