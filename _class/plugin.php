<?php

/**
 * Class to handle plug-ins
 *
 * @author Jacob Rogaishio
 * 
 */
class template
{
	// Properties
	public $id = null;
	public $path = null;
	public $file = null;
	public $name = null;
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
		if(isset($params['path'])) $this->path = $params['path'];
		if(isset($params['file'])) $this->file = $params['file'];
		if(isset($params['name'])) $this->name = $params['name'];

		$this->constr = true;
	}

	/**
	 * Inserts the current plugin object into the database
	 */
	public function insert() {
		if($this->constr) {
			
			$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES";
			$sql .= "('$this->path', '$this->file', '$this->name','" . time() . "')";

			$result = $this->conn->query($sql) OR DIE ("Could not create template!");
			if($result) {
				echo "<span class='update_notice'>Created template successfully!</span><br /><br />";
			}
		} else {
			echo "Failed to load fornm data!";
		}
	}

	/**
	 * Updates the current plugin object in the database.
	 * 
	 * @param $plugId	The plugin Id to update
	 */
	public function update($plugId) {
	
		if($this->constr) {

			$sql = "UPDATE templates SET
			template_path = '$this->path', 
			template_file = '$this->file', 
			template_name = '$this->name'
			WHERE id=$plugId;
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
	 * Deletes the current plugin object from the database.
	 * 
	 * @param $plugId	The plugin to be deleted
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete($plugId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($plugId);
		echo "<span class='update_notice'>Template deleted! Bye bye '$this->name', we will miss you.<br />Please be sure to update any pages that were using this template!</span><br /><br />";
		
		$templateSQL = "DELETE FROM templates WHERE id=$plugId";
		$templateResult = $this->conn->query($templateSQL);
		
		return $templateResult;
	}
	
	/**
	 * Loads the plugin object members based off the plugin id in the database
	 * 
	 * @param $pluginId	The plugin to be loaded
	 */
	public function loadRecord($pluginId) {
		if(isset($pluginId) && $pluginId != "new") {
			
			$templateSQL = "SELECT * FROM templates WHERE id=$pluginId";
				
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
	 * Builds the admin editor form to add / update plugins
	 * 
	 * @param $pluginId	The plugin to be edited
	 */
	public function buildEditForm($pluginId) {

		//Load the page from an ID
		$this->loadRecord($pluginId);

		echo "<div id='main_content'>";
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
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($pluginId) || $pluginId == "new") ? "Create" : "Update") . ' This Template!" /><br /><br />
			' . ((isset($pluginId) && $pluginId != "new") ? '<a href="admin.php?type=template&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Template!</a><br /><br />' : '') . '
			</form>
		';
		echo "</div>";
		
		echo "<div id='main_tools'>";
		echo "<h2>Admin Actions</h2><br /><br />";
		
		echo '<a href="admin.php" class="actionLink">Back to Home</a><br /><br />';
		echo "</div><div class='clear'></div>";
		
		
	}
	
}

?>

