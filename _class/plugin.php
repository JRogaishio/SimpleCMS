<?php

/**
* Class to handle articles
*/

class template
{
	// Properties
	public $id = null;
	public $path = null;
	public $file = null;
	public $name = null;

	/**
	* Sets the object's properties using the values in the supplied array
	*
	* @param assoc The property values
	*/
	public function __construct($data=array()) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($data['path'])) $this->path = $data['path'];
		if(isset($data['file'])) $this->file = $data['file'];
		if(isset($data['name'])) $this->name = $data['name'];

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
			
			$sql = "INSERT INTO templates (template_path, template_file, template_name, template_created) VALUES";
			$sql .= "('$this->path', '$this->file', '$this->name','" . time() . "')";

			$result = mysql_query($sql) OR DIE ("Could not create template!");
			if($result) {
				echo "<span class='update_notice'>Created template successfully!</span><br /><br />";
			}
		} else {
			echo "Failed to load fornm data!";
		}
	}

	/**
	* Updates the current page object in the database.
	*/
	public function update($templateId) {
	
		if($this->constr) {
			mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysql_error());
			mysql_select_db(DB_NAME) or die("Could not select database. " . mysql_error());

			$sql = "UPDATE templates SET
			template_path = '$this->path', 
			template_file = '$this->file', 
			template_name = '$this->name'
			WHERE id=$templateId;
			";

			$result = mysql_query($sql) OR DIE ("Could not update template!");
			if($result) {
				echo "<span class='update_notice'>Updated template successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load fornm data!";
		}

	}

	/**
	* Deletes the current page object from the database.
	*/
	public function delete($templateId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($templateId);
		echo "<span class='update_notice'>Template deleted! Bye bye '$this->name', we will miss you.<br />Please be sure to update any pages that were using this template!</span><br /><br />";
		
		$templateSQL = "DELETE FROM templates WHERE id=$templateId";
		$templateResult = mysql_query($templateSQL);
		
		return $templateResult;
	}
	
	public function loadRecord($templateId) {
		if(isset($templateId) && $templateId != "new") {
			
			$templateSQL = "SELECT * FROM templates WHERE id=$templateId";
				
			$templateResult = mysql_query($templateSQL);

			if ($templateResult !== false && mysql_num_rows($templateResult) > 0 )
				$row = mysql_fetch_assoc($templateResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->path = $row['template_path'];
				$this->file = $row['template_file'];
				$this->name = $row['template_name'];
			}
			
			$this->constr = true;
		}
	
	}
	
	public function buildEditForm($templateId) {

		//Load the page from an ID
		$this->loadRecord($templateId);

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
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($templateId) || $templateId == "new") ? "Create" : "Update") . ' This Template!" /><br /><br />
			' . ((isset($templateId) && $templateId != "new") ? '<a href="admin.php?type=template&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Template!</a><br /><br />' : '') . '
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
