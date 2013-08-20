<?php

/**
* Class to handle articles
*/

class user
{
	// Properties
	public $id = null;
	public $loginname = null;
	public $password = null;
	public $salt = null;
	public $email = null;
	public $isRegistered = null;
	
	/**
	* Sets the object's properties using the values in the supplied array
	*
	* @param assoc The property values
	*/
	public function __construct($data=array()) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($data['username'])) $this->loginname = $data['username'];
		if(isset($data['password'])) $this->password = $data['password'];
		if(isset($data['email'])) $this->email = $data['email'];

		$this->constr = true;
	}

	/**
	* Sets the object's properties using the edit form post values in the supplied array
	*
	* @param assoc The form post values
	*/
	public function storeFormValues ($params) {
		// Store all the parameters
		$this->__construct($params);
	}

	/**
	* Inserts the current page object into the database, and sets its ID property.
	*/
	public function insert() {
		if($this->constr) {
			mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysql_error());
			mysql_select_db(DB_NAME) or die("Could not select database. " . mysql_error());
			$salt = unique_salt();
			$secPass = hash('sha256',$this->password);
			$secPass = hash('sha256',($secPass . $salt));
			
			$sql = "INSERT INTO users (user_login, user_pass, user_salt, user_email,user_created, user_isRegistered) VALUES";
			$sql .= "('$this->loginname', '$secPass', '$salt', '$this->email','" . time() . "', 1)";

			$result = mysql_query($sql) OR DIE ("Could not create user!");
			if($result) {
				echo "<span class='update_notice'>Created user successfully!</span><br /><br />";
			}
			

		} else {
			echo "Failed to load fornm data!";
		}
	}

	/**
	* Updates the current page object in the database.
	*/
	public function update($userId) {
	
		if($this->constr) {
			mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysql_error());
			mysql_select_db(DB_NAME) or die("Could not select database. " . mysql_error());

			
			$secPass = hash('sha256',$this->password);
			$secPass = hash('sha256',($secPass . get_userSalt($this->loginname)));
			
			$sql = "UPDATE users SET
			user_login = '$this->loginname', 
			user_pass = '$secPass', 
			user_email = '$this->email'
			WHERE id=$userId;
			";

			$result = mysql_query($sql) OR DIE ("Could not update user!");
			if($result) {
				echo "<span class='update_notice'>Updated user successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load fornm data!";
		}
	}

	/**
	* Deletes the current page object from the database.
	*/
	public function delete($userId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($userId);
		echo "<span class='update_notice'>User deleted! Bye bye '$this->loginname', we will miss you.</span><br /><br />";
		
		$userSQL = "DELETE FROM users WHERE id=$userId";
		$userResult = mysql_query($userSQL);
		
		return $userResult;
	}
	
	public function loadRecord($userId) {
		if(isset($userId) && $userId != "new") {
			
			$userSQL = "SELECT * FROM users WHERE id=$userId";
				
			$userResult = mysql_query($userSQL);

			if ($userResult !== false && mysql_num_rows($userResult) > 0 )
				$row = mysql_fetch_assoc($userResult);

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

			<label for="password">Password:</label><br />
			<input name="password" id="password" type="password" maxlength="150" value="" />
			<div class="clear"></div>
			<br />
			<label for="email">Email Address:</label><br />
			<input name="email" id="email" type="text" maxlength="150" value="' . $this->email . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($userId) || $userId == "new") ? "Create" : "Update") . ' This User!" /><br /><br />
			' . ((isset($userId) && $userId != "new") ? '<a href="admin.php?type=user&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This User!</a><br /><br />' : '') . '
			</form>
		';
	}
	
}

?>
