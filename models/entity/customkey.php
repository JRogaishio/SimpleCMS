<?php

/**
 * Class to handle page keys
 *
 * @author Jacob Rogaishio
 * 
 */
class customkey extends model
{
	// Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $keyItem = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"keyItem");
	protected $keyValue = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"keyValue");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	//Getters
	/*public function getId() {return $this->get($this->id);}
	public function getKey() {return $this->get($this->keyItem);}
	public function getValue() {return $this->get($this->keyValue);}
	public function getCreated() {return $this->get($this->created);}
	
	//Setters
	public function setId($val) {$this->set($this->id, $val);}
	public function setKey($val) {$this->set($this->keyItem, $val);}
	public function setValue($val) {$this->set($this->keyValue, $val);}
	public function setCreated($val) {$this->set($this->created, $val);}*/
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['key'])) $this->key = clean($this->conn, $params['key']);
		if(isset($params['value'])) $this->value = clean($this->conn, $params['value']);

		$this->constr = true;
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
		$ret = "";
		
		if($this->key == "") {
			$ret = "Please enter a key.";
		} else if($this->value == "") {
			$ret = "Please enter a value for this key";
		}
		return $ret;
	}
	
	/**
	 * Inserts the current key object into the database
	 * 
	 * @return Returns true on validation success or false on failure
	 * 
	 */
	public function insert() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
			
				$sql = "INSERT INTO " . $this->table . " (key_name, key_value, key_created) VALUES";
				$sql .= "('$this->key', '$this->value','" . time() . "')";

				$result = $this->conn->query($sql) OR DIE ("Could not create key!");
				if($result) {
					echo "<span class='update_notice'>Created key successfully!</span><br /><br />";
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
	 * Updates the current key object in the database.
	 * 
	 */
	public function update() {
	
		if($this->constr) {

			$sql = "UPDATE " . $this->table . " SET
			key_name = '$this->key', 
			key_value = '$this->value' 
			WHERE id=" . $this->id . ";";
			
			$result = $this->conn->query($sql) OR DIE ("Could not update key!");
			if($result) {
				echo "<span class='update_notice'>Updated key successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load form data!";
		}
	}

	/**
	 * Deletes the current key object from the database.
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete() {
		echo "<span class='update_notice'>Key deleted! Bye bye '$this->key', we will miss you.<br />Please be sure to update any pages that were using this key!</span><br /><br />";
		
		$keySQL = "DELETE FROM " . $this->table . " WHERE id=" . $this->id;
		$keyResult = $this->conn->query($keySQL);
		
		return $keyResult;
	}
	
	/**
	 * Loads the key object members based off the key id in the database
	 * 
	 * @param $keyId	The key to be loaded
	 */
	public function loadRecord($keyId) {
		//Set a field to use by the logger
		$this->logField = &$this->key;
		
		if(isset($keyId) && $keyId != null) {
			
			$keySQL = "SELECT * FROM " . $this->table . " WHERE id=$keyId";
				
			$keyResult = $this->conn->query($keySQL);

			if ($keyResult !== false && mysqli_num_rows($keyResult) > 0 )
				$row = mysqli_fetch_assoc($keyResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->key = $row['key_name'];
				$this->value = $row['key_value'];
			}
			
			$this->constr = true;
		}
	
	}
	
	/**
	 * Builds the admin editor form to add / update key
	 * 
	 * @param $keyID	The key to be edited
	 */
	public function buildEditForm($keyId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($keyId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=customkey&action=read">Key List</a> > <a href="admin.php?type=customkey&action=update&p=' . $keyId . '">Key</a><br /><br />';

		
		echo '
			<form action="admin.php?type=customkey&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

			<label for="key" title="This is the key name">Key name:</label><br />
			<input name="key" id="key" type="text" maxlength="150" value="' . $this->key . '" />
			<div class="clear"></div>

			<label for="value" title="This is value of the key">Value:</label><br />
			<input name="value" id="value" type="text" maxlength="150" value="' . $this->value . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($keyId) || $keyId == null) ? "Create" : "Update") . ' This Key!" /><br /><br />
			' . ((isset($keyId) && $keyId != null) ? '<a href="admin.php?type=customkey&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Key!</a><br /><br />' : '') . '
			</form>
		';
	}
	
	/**
	 * Display the list of all templates
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=customkey&action=read">Key List</a><br /><br />';
	
		$keySQL = "SELECT * FROM " . $this->table . " ORDER BY key_created DESC";
		$keyResult = $this->conn->query($keySQL);
	
		if ($keyResult !== false && mysqli_num_rows($keyResult) > 0 ) {
			while($row = mysqli_fetch_assoc($keyResult) ) {
	
				$name = stripslashes($row['key_name']);
				$value = stripslashes($row['key_value']);
	
				echo "
				<div class=\"key\">
					<h2>
					<a href=\"admin.php?type=customkey&action=update&p=".$row['id']."\" title=\"Edit / Manage this key\" alt=\"Edit / Manage this key\" class=\"cms_pageEditLink\" >$name</a>
						</h2>
						<p>" . $value . "</p>
				</div>";
	
			}
			} else {
			echo "
			<p>
				No keys found!
			</p>";
		}
	
			}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `key` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `key_name` varchar(128) DEFAULT NULL,
		  `key_value` text,
		  `key_created` varchar(128) DEFAULT NULL,
		
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");	
	}
	
}

?>
