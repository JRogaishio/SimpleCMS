<?php

/**
 * Class to handle website information
 *
 * @author Jacob Rogaishio
 * 
 */
class site
{
	// Properties
	public $id = null;
	public $name = null;
	public $linkFormat = null;
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
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['name'])) $this->name = clean($this->conn, $params['name']);
		if(isset($params['linkFormat'])) $this->linkFormat = clean($this->conn, $params['linkFormat']);
		$this->constr = true;
	}

	
	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
		$ret = "";
	
		if($this->name == "") {
			$ret = "Please enter a site name.";
		}
	
		return $ret;
	}
	
	/**
	 * Updates the current site object in the database.
	 * 
	 * @param $siteId	The site Id to update
	 */
	public function update($siteId) {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$sql = "UPDATE sites SET
				site_name = '$this->name', 
				site_linkFormat = '$this->linkFormat'
				WHERE id=$siteId;
				";
	
				$result = $this->conn->query($sql) OR DIE ("Could not update site!");
				if($result) {
					echo "<span class='update_notice'>Updated site successfully!</span><br /><br />";
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
	 * Loads the site object members based off the site id in the database
	 * 
	 * @param $siteId	The site to be loaded
	 */
	public function loadRecord($siteId) {
		if(isset($siteId) && $siteId != "new") {
			
			$siteSQL = "SELECT * FROM sites WHERE id=$siteId";
				
			$siteResult = $this->conn->query($siteSQL);

			if ($siteResult !== false && mysqli_num_rows($siteResult) > 0 )
				$row = mysqli_fetch_assoc($siteResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->name = $row['site_name'];
				$this->linkFormat = $row['site_linkFormat'];
			}
			
			$this->constr = true;
		}
	}
	
	/**
	 * Builds the admin editor form to update the site
	 * 
	 * @param $siteId	The site to be edited
	 */
	public function buildEditForm($siteId) {

		//Load the site from an ID
		$this->loadRecord($siteId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=siteDisplay">Site List</a> > <a href="admin.php?type=site&action=update&p=' . $siteId . '">Site</a><br /><br />';

		echo '
			<form action="admin.php?type=site&action=update&p=' . $this->id . '" method="post">

			<label for="name" title="This is ...">Site name:</label><br />
			<input name="name" id="name" type="text" maxlength="150" value="' . $this->name . '" />
			<div class="clear"></div>

			<label for="linkFormat" title="This is the link format">Link format:</label><br />
			<select name="linkFormat" id="linkFormat">
				<option selected value="' . $this->linkFormat . '">-- ' .($this->linkFormat=="clean"?"website.com/page/MyPage":($this->linkFormat=="raw"?"website.com/index.php?p=MyPage":"ERROR - UNKNOWN FORMAT TYPE")) . ' --</option>
				<option value="clean">website.com/page/MyPage</option>
				<option value="raw">website.com/index.php?p=MyPage</option>
			</select>

			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($siteId) || $siteId == "new") ? "Create" : "Update") . ' This Site!" /><br /><br />
			</form>
		';
	}
	
}

?>


