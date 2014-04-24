<?php

/**
 * Class to handle page templates
 *
 * @author Jacob Rogaishio
 * 
 */
class group extends model
{
	// Properties
	protected $table = "group";
	protected $id = null;
	protected $name = null;
	protected $edit = null;
	
	//Getters
	public function getId() {return $this->id;}
	public function getName() {return $this->name;}
	public function getEdit() {return $this->edit;}
	
	//Setters
	public function setId($val) {$this->id = $val;}
	public function setName($val) {$this->name = $val;}
	public function setEdit($val) {$this->edit = $val;}
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['name'])) $this->name = clean($this->conn, $params['name']);
		if(isset($params['edit'])) $this->edit = clean($this->conn, $params['edit']);

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
			$ret = "Please enter a title.";
		}
	
		return $ret;
	}
	
	/**
	 * Inserts the current template object into the database
	 */
	public function insert() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
			
				$sql = "INSERT INTO " . $this->table . " (template_path, template_file, template_name, template_created) VALUES";
				$sql .= "('$this->path', '$this->file', '$this->name','" . time() . "')";
	
				$result = $this->conn->query($sql) OR DIE ("Could not create template!");
				if($result) {
					echo "<span class='update_notice'>Created template successfully!</span><br /><br />";
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
	 * Updates the current group object in the database.
	 * 
	 * @param $templateId	The template Id to update
	 */
	public function update() {
	
		if($this->constr) {

			$sql = "UPDATE " . $this->table . " SET
			template_path = '$this->path', 
			template_file = '$this->file', 
			template_name = '$this->name'
			WHERE id=" . $this->id . ";";

			$result = $this->conn->query($sql) OR DIE ("Could not update " . $this->table . "!");
			if($result) {
				echo "<span class='update_notice'>Updated template successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load form data!";
		}
	}

	/**
	 * Deletes the current template object from the database.
	 * 
	 * @param $templateId	The template to be deleted
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete() {
		echo "<span class='update_notice'>Template deleted! Bye bye '$this->name', we will miss you.<br />Please be sure to update any pages that were using this template!</span><br /><br />";
		
		$templateSQL = "DELETE FROM " . $this->table . " WHERE id=" . $this->id;
		$templateResult = $this->conn->query($templateSQL);
		
		return $templateResult;
	}
	
	/**
	 * Loads the template object members based off the template id in the database
	 * 
	 * @param $templateId	The template to be loaded
	 */
	public function loadRecord($templateId) {
		if(isset($templateId) && $templateId != null) {
			
			$templateSQL = "SELECT * FROM " . $this->table . " WHERE id=$templateId";
				
			$templateResult = $this->conn->query($templateSQL);

			if ($templateResult !== false && mysqli_num_rows($templateResult) > 0 )
				$row = mysqli_fetch_assoc($templateResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->path = $row['template_path'];
				$this->file = $row['template_file'];
				$this->name = $row['template_name'];
			}
			
			$this->constr = true;
		}
	
	}
	
	/**
	 * Builds the admin editor form to add / update templates
	 * 
	 * @param $templateId	The template to be edited
	 */
	public function buildEditForm($groupId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($groupId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=permissionDisplay">Permission Group List</a> > <a href="admin.php?type=template&action=update&p=' . $groupId . '">Permission Group</a><br /><br />';

		
		echo '
			<form action="admin.php?type=permission&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

			<label for="name" title="This is the group name">Group name:</label><br />
			<input name="name" id="path" type="text" maxlength="150" value="' . $this->name . '" />
			<div class="clear"></div>


					
			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($templateId) || $templateId == null) ? "Create" : "Update") . ' This Group!" /><br /><br />
			' . ((isset($groupId) && $groupId != null) ? '<a href="admin.php?type=permission&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Group!</a><br /><br />' : '') . '
			</form>
		';
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `templates` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `group_name` varchar(128) DEFAULT NULL,
		  `group_edit` tinyint(1) DEFAULT NULL,
		
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
		
		/*Insert default data for `templates` if we dont have one already*/
		/*if(countRecords($this->conn, "templates") == 0) {
			$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES('default_example', 'index.php', 'Default Example Template', '" . time() . "')";
			$this->conn->query($sql) OR DIE ("Could not insert default example data into \"templates\"");
		}*/
		
	}
	
}

?>
