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
		if(isset($params['id'])) $this->setId(clean($this->conn, $params['id']));
		if(isset($params['groupId'])) $this->setGroupId(clean($this->conn, $params['groupId']));
		if(isset($params['model'])) $this->setModel(clean($this->conn, $params['model']));
		(isset($params['read'])) ? $this->setReadAction(clean($this->conn, $params['read'])) : $this->setReadAction(0);
		(isset($params['insert'])) ? $this->setInsertAction(clean($this->conn, $params['insert'])) : $this->setInsertAction(0);
		(isset($params['update'])) ? $this->setUpdateAction(clean($this->conn, $params['update'])) : $this->setUpdateAction(0);
		(isset($params['delete'])) ? $this->setDeleteAction(clean($this->conn, $params['delete'])) : $this->setDeleteAction(0);
		
		$this->constr = true;
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
		
		return $ret;
	}
	
	/**
	 * Inserts the current template object into the database
	 */
	public function insert() {
		$ret = true;
		if($this->constr) {
			if( $this->getGroupId() == null ||  $this->getGroupId() == '') {$this->setGroupId(getLastField($this->conn, 'permissiongroup', 'id'));}
			
			$this->setCreated(time());
			$ret = $this->save();
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
		$this->logField = $this->getId();
		
		if(isset($groupId) && $groupId != null && isset($this->model) && $this->model != null) {
			
			$permissionSQL = "SELECT * FROM " . $this->table . " WHERE groupId=$groupId AND model='" . $this->getModel() . "'";
				
			$permissionResult = $this->conn->query($permissionSQL);

			if ($permissionResult !== false && mysqli_num_rows($permissionResult) > 0 )
				$row = mysqli_fetch_assoc($permissionResult);

			if(isset($row)) {
				$this->load($row['id']);
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
}

?>
