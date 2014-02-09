<?php

/**
 * Class to handle users
 *
 * @author Jacob Rogaishio
 * 
 */
class user
{
	// Properties
	public $id = null;
	public $loginname = null;
	public $password = null;
	public $password2 = null;
	public $salt = null;
	public $email = null;
	public $isRegistered = null;
	private $conn = null; //Database connection object
	
	/**
	 * Stores the connection object in a local variable on construction
	 *
	 * @param dbConn The property values
	 */
	public function __construct($dbConn) {
		$this->conn = $dbConn;
	}

	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params=array()) {
		// Store all the parameters
		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['username'])) $this->loginname = clean($this->conn, $params['username']);
		if(isset($params['password'])) $this->password = clean($this->conn, $params['password']);
		if(isset($params['password2'])) $this->password2 = clean($this->conn, $params['password2']);
		if(isset($params['email'])) $this->email = clean($this->conn, $params['email']);

		$this->constr = true;
	}

	/**
	 * validate the fields
	 * 
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
		$ret = "";
		
		if($this->loginname == "") {
			$ret = "Please enter a username.";
		} else if($this->password == "") {
			$ret = "Please enter a password.";
		} else if($this->password != $this->password2) {
			$ret = "The passwords don't match.";
		} else if($this->email == "") {
			$ret = "Please enter an email.";
		} else if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
			$ret = "Email address is not valid.";
		}
		
		return $ret;
	}
	
	/**
	 * Inserts the current user object into the database
	 */
	public function insert() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$salt = unique_salt();
				$secPass = hash('sha256',$this->password);
				$secPass = hash('sha256',($secPass . $salt));
				
				$sql = "INSERT INTO users (user_login, user_pass, user_salt, user_email,user_created, user_isRegistered) VALUES";
				$sql .= "('$this->loginname', '$secPass', '$salt', '$this->email','" . time() . "', 1)";
				
				$result = $this->conn->query($sql) OR DIE ("Could not create user!");
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
	 */
	public function update($userId) {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$secPass = hash('sha256',$this->password);
				$secPass = hash('sha256',($secPass . get_userSalt($this->conn, $this->loginname)));
				
				$sql = "UPDATE users SET
				user_login = '$this->loginname', 
				user_pass = '$secPass', 
				user_email = '$this->email'
				WHERE id=$userId;
				";
	
				$result = $this->conn->query($sql) OR DIE ("Could not update user!");
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
	public function delete($userId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($userId);
		echo "<span class='update_notice'>User deleted! Bye bye '$this->loginname', we will miss you.</span><br /><br />";
		
		$userSQL = "DELETE FROM users WHERE id=$userId";
		$userResult = $this->conn->query($userSQL);
		
		return $userResult;
	}
	
	/**
	 * Loads the user object members based off the user id in the database
	 * 
	 * @param $userId	The user to be loaded
	 */
	public function loadRecord($userId) {
		if(isset($userId) && $userId != "new") {
			
			$userSQL = "SELECT * FROM users WHERE id=$userId";
				
			$userResult = $this->conn->query($userSQL);

			if ($userResult !== false && mysqli_num_rows($userResult) > 0 )
				$row = mysqli_fetch_assoc($userResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->loginname = $row['user_login'];
				$this->password = $row['user_pass'];
				$this->salt = $row['user_salt'];
				$this->email = $row['user_email'];
				$this->isRegistered = $row['user_isRegistered'];
			}
			
			$this->constr = true;
		}
	
	}
	
	/**
	 * Builds the admin editor form to add / update users
	 * 
	 * @param $userId	The user to be edited
	 */
	public function buildEditForm($userId) {

		//Load the page from an ID
		$this->loadRecord($userId);
		if($userId != "new")
			echo '<a href="admin.php">Home</a> > <a href="admin.php?type=userDisplay">User List</a> > <a href="admin.php?type=user&action=update&p=' . $userId . '">User</a><br /><br />';

		echo '
			<form action="admin.php?type=user&action=update&p=' . $this->id . '" method="post">

			<label for="username">Username:</label><br />
			<input name="username" id="username" class="cms_username"type="text" maxlength="150" value="' . $this->loginname . '" ' . ($this->loginname != null ? "readonly=readonly" : "") . ' />
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
			<input name="email" id="email" type="text" maxlength="150" value="' . $this->email . '" />
			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($userId) || $userId == "new") ? "Create" : "Update") . ' This User!" /><br /><br />
			' . ((isset($userId) && $userId != "new") ? '<a href="admin.php?type=user&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This User!</a><br /><br />' : '') . '
			</form>
		';
	}
	
}

?>

