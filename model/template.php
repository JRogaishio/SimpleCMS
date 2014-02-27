<?php

/**
 * Class to handle page templates
 *
 * @author Jacob Rogaishio
 * 
 */
class template extends model
{
	// Properties
	public $id = null;
	public $path = null;
	public $file = null;
	public $name = null;
	

	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['path'])) $this->path = clean($this->conn, $params['path']);
		if(isset($params['file'])) $this->file = clean($this->conn, $params['file']);
		if(isset($params['name'])) $this->name = clean($this->conn, $params['name']);

		$this->constr = true;
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
		$ret = "";
		
		if($this->path == "") {
			$ret = "Please enter a folder name in _template/.";
		} else if(strpos($this->path, " ") !== false) {
			$ret = "The path cannot contain any spaces.";
		} else if($this->file == "") {
			$ret = "Please enter a file to load in template folder (ex. index.php).";
		} else if(strpos($this->file, " ") !== false) {
			$ret = "The file cannot contain any spaces.";
		} else if($this->name == "") {
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
			
				$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES";
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
	 * Updates the current template object in the database.
	 * 
	 * @param $templateId	The template Id to update
	 */
	public function update($templateId) {
	
		if($this->constr) {

			$sql = "UPDATE templates SET
			template_path = '$this->path', 
			template_file = '$this->file', 
			template_name = '$this->name'
			WHERE id=$templateId;
			";

			$result = $this->conn->query($sql) OR DIE ("Could not update template!");
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
	public function delete($templateId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($templateId);
		echo "<span class='update_notice'>Template deleted! Bye bye '$this->name', we will miss you.<br />Please be sure to update any pages that were using this template!</span><br /><br />";
		
		$templateSQL = "DELETE FROM templates WHERE id=$templateId";
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
			
			$templateSQL = "SELECT * FROM templates WHERE id=$templateId";
				
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
	public function buildEditForm($templateId) {

		//Load the page from an ID
		$this->loadRecord($templateId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=templateDisplay">Template List</a> > <a href="admin.php?type=template&action=update&p=' . $templateId . '">Tenplate</a><br /><br />';

		
		echo '
			<form action="admin.php?type=template&action=update&p=' . $this->id . '" method="post">

			<label for="path" title="This is the name in _template">Template folder name:</label><br />
			<input name="path" id="path" type="text" maxlength="150" value="' . $this->path . '" />
			<div class="clear"></div>

			<label for="file" title="This is the name of the template php file">Template filename:</label><br />
			<input name="file" id="file" type="text" maxlength="150" value="' . $this->file . '" />
			<div class="clear"></div>

			<label for="name" title="This is the name that will appear when selecting a template">Display name:</label><br />
			<input name="name" id="name" type="text" maxlength="150" value="' . $this->name . '" />
			<div class="clear"></div>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($templateId) || $templateId == null) ? "Create" : "Update") . ' This Template!" /><br /><br />
			' . ((isset($templateId) && $templateId != null) ? '<a href="admin.php?type=template&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Template!</a><br /><br />' : '') . '
			</form>
		';

		
		
	}
	
	/**
	 * Display the template management page
	 *
	 */
	public function displayManager($action, $parent, $child, $user, $log, $auth=null) {
		$ret = false;
		switch($action) {
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
						
					$this->storeFormValues($_POST);
						
					if($parent == null) {
						$result = $this->insert();
	
						if(!$result) {
							$this->buildEditForm($parent);
						} else {
							$this->buildEditForm(getLastField($this->conn,"templates", "id"));
							$log->trackChange("template", 'add',$user->id,$user->loginname, $this->name . " added");
						}
					} else {
						$result = $this->update($parent);
						//Re-build the page creation form once we are done
						$this->buildEditForm($parent);
	
						if($result) {
							$log->trackChange("template", 'update',$user->id,$user->loginname, $this->name . " updated");
						}
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$this->buildEditForm($parent);
				}
				break;
			case "delete":
				$this->delete($parent);
				$ret = true;
				$log->trackChange("template", 'delete',$user->id,$user->loginname, $this->name . " deleted");
				break;
			default:
				echo "Error with template manager<br /><br />";
				$ret = true;
		}
		return $ret;
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `templates` */
		$sql = "CREATE TABLE IF NOT EXISTS `templates` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `template_path` varchar(128) DEFAULT NULL,
		  `template_file` varchar(128) DEFAULT NULL,
		  `template_name` varchar(64) DEFAULT NULL,
		  `template_created` varchar(128) DEFAULT NULL,
		
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"templates\"");
		
		/*Insert default data for `templates` if we dont have one already*/
		if(countRecords($this->conn, "templates") == 0) {
			$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES('_default', 'index.php', 'Default', '" . time() . "')";
			$this->conn->query($sql) OR DIE ("Could not insert default data into \"templates\"");
		}
		
	}
	
}

?>


