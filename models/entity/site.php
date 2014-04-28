<?php

/**
 * Class to handle website information
 *
 * @author Jacob Rogaishio
 * 
 */
class site extends model
{
	// Properties
	protected $table = "site";
	protected $id = null;
	protected $name = null;
	protected $linkFormat = null;

	//Getters
	public function getId() {return $this->id;}
	public function getName() {return $this->name;}
	public function getLinkFormat() {return $this->linkFormat;}
	
	//Setters
	public function setId($val) {$this->id = $val;}
	public function setName($val) {$this->name = $val;}
	public function setLinkFormat($val) {$this->linkFormat = $val;}

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
	 * 
	 * @return returns true if the update was successful
	 */
	public function update() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$sql = "UPDATE " . $this->table . " SET
				site_name = '$this->name', 
				site_linkFormat = '$this->linkFormat'
				WHERE id=" . $this->id . ";";
	
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
		//Set a field to use by the logger
		$this->logField = &$this->name;
		
		if(isset($siteId) && $siteId != null) {
			
			$siteSQL = "SELECT * FROM " . $this->table . " WHERE id=$siteId";
				
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
	public function buildEditForm($siteId, $child=null, $user=null) {

		//Load the site from an ID
		$this->loadRecord($siteId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=siteDisplay">Site List</a> > <a href="admin.php?type=site&action=update&p=' . $siteId . '">Site</a><br /><br />';

		echo '
			<form action="admin.php?type=site&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

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
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($siteId) || $siteId == null) ? "Create" : "Update") . ' This Site!" /><br /><br />
			</form>
		';
	}
	
	/**
	 * Display the site management page
	 * 
	 * @param $action	The action to be performed such as update or delete
	 * @param $parent	The ID of the site object to be edited. This is the p GET Data
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
		switch($action) {
			case "read":
				if($user->checkPermission($this->table, 'read', false)) {
					$this->displayModelList();
				} else {
					echo "You do not have permissions to '<strong>read</strong>' records for " . $this->table . ".<br />";
				}
				break;
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the site edit form: save the new article
					$this->storeFormValues($_POST);
						
					$result = $this->update($parent);
					//Re-build the site creation form once we are done
					$this->buildEditForm($parent);
					if($result) {
						$this->log->trackChange($this->table, 'update',$user->getId(),$user->getLoginname(), $this->name . " updated");
					}
				} else {
					// User has not posted the site edit form yet: display the form
					$this->buildEditForm($parent);
				}
				break;
			default:
				echo "Error with site manager<br /><br />";
				$ret = true;
		}
		return $ret;
	}
	
	/**
	 * Display the site manager
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=site&action=read">Site</a><br /><br />';
	
		$siteSQL = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
		$siteResult = $this->conn->query($siteSQL);
	
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
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `site` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `site_name` varchar(64) DEFAULT NULL,
		  `site_linkFormat` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
		
		
		/*Insert site data for `site` if we dont have one already*/
		if(countRecords($this->conn, $this->table) == 0) {
			$sql = "INSERT INTO " . $this->table . " (site_name, site_linkFormat) VALUES('My FerretCMS Website', 'clean')";
			$this->conn->query($sql) OR DIE ("Could not insert default data into \"site\"");
		}
	
	}
}

?>


