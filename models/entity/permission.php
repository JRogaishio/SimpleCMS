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
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $groupId = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"groupId");
	protected $model = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"model");
	protected $readAction = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"readAction");
	protected $insertAction = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"insertAction");
	protected $updateAction = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"updateAction");
	protected $deleteAction = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"deleteAction");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {	
		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['id'])) $this->id = clean($this->conn, $params['id']);
		if(isset($params['groupId'])) $this->groupId = clean($this->conn, $params['groupId']);
		if(isset($params['model'])) $this->model = clean($this->conn, $params['model']);
		(isset($params['read'])) ? $this->read = clean($this->conn, $params['read']) : $this->read = 0;
		(isset($params['insert'])) ? $this->insert = clean($this->conn, $params['insert']) : $this->insert = 0;
		(isset($params['update'])) ? $this->update = clean($this->conn, $params['update']) : $this->update = 0;
		(isset($params['delete'])) ? $this->delete = clean($this->conn, $params['delete']) : $this->delete = 0;
		
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
			
			$sql = "INSERT INTO " . $this->table . " (permission_groupId, permission_model, permission_read, permission_insert, permission_update, permission_delete, permission_created) VALUES";
			$sql .= "('$this->groupId', '$this->model', $this->read, $this->insert, $this->update, $this->delete, '" . time() . "')";

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
			permission_read = $this->read, 
			permission_insert = $this->insert,
			permission_update = $this->update, 
			permission_delete = $this->delete
			WHERE id=" . $this->id . ";";

			$result = $this->conn->query($sql) OR DIE ("Could not update " . $this->table . "!");

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
		$permissionSQL = "DELETE FROM " . $this->table . " WHERE permission_groupId=" . $this->groupId;

		$permissionResult = $this->conn->query($permissionSQL);
		
		return $permissionResult;
	}
	
	/**
	 * Loads the template object members based off the template id in the database
	 * 
	 * @param $templateId	The template to be loaded
	 */
	public function loadRecord($groupId, $c=null) {
		//Set a field to use by the logger
		$this->logField = &$this->id;
		
		if(isset($groupId) && $groupId != null && isset($this->model) && $this->model != null) {
			
			$permissionSQL = "SELECT * FROM " . $this->table . " WHERE permission_groupId=$groupId AND permission_model='$this->model'";
				
			$permissionResult = $this->conn->query($permissionSQL);

			if ($permissionResult !== false && mysqli_num_rows($permissionResult) > 0 )
				$row = mysqli_fetch_assoc($permissionResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->groupId = $row['permission_groupId'];
				$this->model = $row['permission_model'];
				$this->read = $row['permission_read'];
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
		//Uses buildPermissionForm() in permissiongroup instead		
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
		  `permission_read` tinyint(1) DEFAULT NULL,
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
