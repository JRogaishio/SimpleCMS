<?php

/**
 * Class to handle page templates
 *
 * @author Jacob Rogaishio
 * 
 */
class permission extends model
{
	// Properties
	protected $table = "permission";
	protected $id = null;
	protected $groupId = null;
	protected $model = null;
	protected $view = null;
	protected $insert = null;
	protected $update = null;
	protected $delete = null;
	
	//Getters
	public function getId() {return $this->id;}
	public function getGroupId() {return $this->groupId;}
	public function getModel() {return $this->model;}
	public function getView() {return $this->view;}
	public function getInsert() {return $this->insert;}
	public function getUpdate() {return $this->update;}
	public function getDelete() {return $this->delete;}
	
	//Setters
	public function setId($val) {$this->id = $val;}
	public function setGroupId($val) {$this->groupId = $val;}
	public function setModel($val) {$this->model = $val;}
	public function setView($val) {$this->view = $val;}
	public function setInsert($val) {$this->insert = $val;}
	public function setUpdate($val) {$this->update = $val;}
	public function setDelete($val) {$this->delete = $val;}

	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {	
		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['groupId'])) $this->groupId = clean($this->conn, $params['groupId']);
		if(isset($params['model'])) $this->model = clean($this->conn, $params['model']);
		(isset($params['view'])) ? $this->view = clean($this->conn, $params['view']) : $this->view = 0;
		(isset($params['insert'])) ? $this->insert = clean($this->conn, $params['insert']) : $this->insert = 0;
		(isset($params['update'])) ? $this->update = clean($this->conn, $params['update']) : $this->update = 0;
		(isset($params['delete'])) ? $this->delete = clean($this->conn, $params['delete']) : $this->delete = 0;

		/*echo "<pre>";
		echo "!!" . $params['view'] . "!!";
		echo "</pre>";
		
		echo "GroupId: " . getLastField($this->conn, 'permissiongroup', 'id') . "<br />";
		echo "Model:   " . $this->model . "<br />";
		echo "View:    " . $this->view . "<br />";
		echo "Insert:  " . $this->insert . "<br />";
		echo "Update:  " . $this->update . "<br />";
		echo "Delete:  " . $this->delete . "<br />";
		echo "##" . convertToBit($params['view']) . "##<br />";
		echo "############";*/
		
		$this->constr = true;
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
		$ret = "";
		
		return $ret;
	}
	
	/**
	 * Inserts the current template object into the database
	 */
	public function insert() {
		$ret = true;
		if($this->constr) {
			if( $this->groupId == null ||  $this->groupId == '') {$this->groupId = getLastField($this->conn, 'permissiongroup', 'id');}
			
			$sql = "INSERT INTO " . $this->table . " (permission_groupId, permission_model, permission_view, permission_insert, permission_update, permission_delete, permission_created) VALUES";
			$sql .= "('$this->groupId', '$this->model', $this->view, $this->insert, $this->update, $this->delete, '" . time() . "')";

			$result = $this->conn->query($sql) OR DIE ("Could not create " . $this->table . "!");

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

			$result = $this->conn->query($sql) OR DIE ("Could not update " . $this->table . "!");
			if($result) {
				echo "<span class='update_notice'>Updated " . $this->table . " successfully!</span><br /><br />";
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
	public function loadRecord($groupId, $model) {
		//Set a field to use by the logger
		$this->logField = &$this->id;
		
		if(isset($groupId) && $groupId != null && isset($model) && $model != null) {
			
			$permissionSQL = "SELECT * FROM " . $this->table . " WHERE permission_groupId=$groupId AND permission_model='$model'";
				
			$permissionResult = $this->conn->query($permissionSQL);

			if ($permissionResult !== false && mysqli_num_rows($permissionResult) > 0 )
				$row = mysqli_fetch_assoc($permissionResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->groupId = $row['permission_groupId'];
				$this->model = $row['permission_model'];
				$this->view = $row['permission_view'];
				$this->insert = $row['permission_insert'];
				$this->update = $row['permission_update'];
				$this->delete = $row['permission_delete'];
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
		  `permission_groupId` int(16) DEFAULT NULL,
		  `permission_model` varchar(128) DEFAULT NULL,
		  `permission_view` tinyint(1) DEFAULT NULL,
		  `permission_insert` tinyint(1) DEFAULT NULL,
		  `permission_update` tinyint(1) DEFAULT NULL,
		  `permission_delete` tinyint(1) DEFAULT NULL,
		  `permission_created` varchar(128) DEFAULT NULL,
				
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
	}
	
}

?>
