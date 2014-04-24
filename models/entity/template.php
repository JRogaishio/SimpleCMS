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
	protected $table = "template";
	protected $id = null;
	protected $path = null;
	protected $file = null;
	protected $name = null;
	
	//Getters
	public function getId() {return $this->id;}
	public function getPath() {return $this->path;}
	public function getFile() {return $this->file;}
	public function getName() {return $this->name;}
	
	//Setters
	public function setId($val) {$this->id = $val;}
	public function setPath($val) {$this->path = $val;}
	public function setFile($val) {$this->file = $val;}
	public function setName($val) {$this->name = $val;}
	
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
	 * Updates the current template object in the database.
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
		//Set a field to use by the logger
		$this->logField = &$this->name;
		
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
	public function buildEditForm($templateId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($templateId);

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=templateDisplay">Template List</a> > <a href="admin.php?type=template&action=update&p=' . $templateId . '">Template</a><br /><br />';

		
		echo '
			<form action="admin.php?type=template&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

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
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `templates` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `template_path` varchar(128) DEFAULT NULL,
		  `template_file` varchar(128) DEFAULT NULL,
		  `template_name` varchar(64) DEFAULT NULL,
		  `template_created` varchar(128) DEFAULT NULL,
		
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
		
		/*Insert default data for `templates` if we dont have one already*/
		if(countRecords($this->conn, $this->table) == 0) {
			$sql = "INSERT INTO " . $this->table . " (template_path, template_file, template_name, template_created) VALUES('default_example', 'index.php', 'Default Example Template', '" . time() . "')";
			$this->conn->query($sql) OR DIE ("Could not insert default example data into \"templates\"");
		}
		
	}
	
}

?>
