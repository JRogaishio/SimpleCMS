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
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['key'])) $this->setKeyItem(clean($this->conn, $params['key']));
		if(isset($params['value'])) $this->setKeyValue(clean($this->conn, $params['value']));

		$this->constr = true;
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
		
		if($this->getKeyItem() == "") {
			$ret = "Please enter a key.";
		} else if($this->getKeyValue() == "") {
			$ret = "Please enter a value for this key";
		}
		return $ret;
	}
	
	/**
	 * Loads the key object members based off the key id in the database
	 * 
	 * @param $keyId	The key to be loaded
	 */
	public function loadRecord($p=null, $c=null) {
		if(isset($p) && $p != null) {
			$this->load($p);
			
			//Set a field to use by the logger
			$this->logField = $this->getKeyItem();
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
			<form action="admin.php?type=customkey&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $this->getId() . '" method="post">

			<label for="key" title="This is the key name">Key name:</label><br />
			<input name="key" id="key" type="text" maxlength="150" value="' . $this->getKeyItem() . '" />
			<div class="clear"></div>

			<label for="value" title="This is value of the key">Value:</label><br />
			<input name="value" id="value" type="text" maxlength="150" value="' . $this->getKeyValue() . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($keyId) || $keyId == null) ? "Create" : "Update") . ' This Key!" /><br /><br />
			' . ((isset($keyId) && $keyId != null) ? '<a href="admin.php?type=customkey&action=delete&p=' . $this->getId() . '"" class="deleteBtn">Delete This Key!</a><br /><br />' : '') . '
			</form>
		';
	}
	
	/**
	 * Display the list of all templates
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=customkey&action=read">Key List</a><br /><br />';
	
		$keySQL = "SELECT * FROM " . $this->table . " ORDER BY created DESC";
		$keyResult = $this->conn->query($keySQL);
	
		if ($keyResult !== false && mysqli_num_rows($keyResult) > 0 ) {
			while($row = mysqli_fetch_assoc($keyResult) ) {
	
				$name = stripslashes($row['keyItem']);
				$value = stripslashes($row['keyValue']);
	
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
}

?>
