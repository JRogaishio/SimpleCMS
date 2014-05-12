<?php

/**
 * Class to handle page templates
 *
 * @author Jacob Rogaishio
 * 
 */
class permissiongroup extends model
{
	// Properties
	//protected $id = null;
	//protected $name = null;
	//protected $edit = 1;
	protected $availModels = array('account', 'customkey', 'permissiongroup', 'log', 'page', 'permission', 'plugin', 'post', 'site', 'template', 'updater', 'uploader');
	protected $permissions = array();
	
	// Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $title = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"title");
	protected $editable = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"editable");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
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

		//Load the permissions into an array
		foreach($this->availModels as $modelName) {
			$permission = new permission($this->conn, $this->log);
			
			//Load permission data from the form
			$values = array('id'=>'', 'groupId'=>$this->id, 'model'=>$modelName, 'read'=>null, 'insert'=>null, 'update'=>null, 'delete'=>null);
			
			if(isset($params[$modelName . '_id'])) $values['id'] = clean($this->conn, $params[$modelName . '_id']);
			if(isset($params[$modelName . '_read'])) $values['read'] = clean($this->conn, $params[$modelName . '_read']);
			if(isset($params[$modelName . '_insert'])) $values['insert'] = clean($this->conn, $params[$modelName . '_insert']);
			if(isset($params[$modelName . '_update'])) $values['update'] = clean($this->conn, $params[$modelName . '_update']);
			if(isset($params[$modelName . '_delete'])) $values['delete'] = clean($this->conn, $params[$modelName . '_delete']);
						
			//Load in the permission data
			$permission->storeFormValues($values);
			
			//Add the loaded permission to the array
			array_push($this->permissions, $permission);

		}
		
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
			$ret = "Please enter a group name.";
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
			
				$sql = "INSERT INTO " . $this->table . " (permissiongroup_name, permissiongroup_edit, permissiongroup_created) VALUES";
				$sql .= "('$this->name', 1,'" . time() . "')";

				$result = $this->conn->query($sql) OR DIE ("Could not create permission group!");
				if($result) {
					echo "<span class='update_notice'>Created permission group successfully!</span><br /><br />";
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
			permissiongroup_name = '$this->name' 
			WHERE id=" . $this->id . ";";

			$result = $this->conn->query($sql) OR DIE ("Could not update " . $this->table . "!");
			if($result) {
				echo "<span class='update_notice'>Updated group successfully!</span><br /><br />";
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
		echo "<span class='update_notice'>Group deleted! Bye bye '$this->name', we will miss you.<br />Please be sure to update any users that were using this group!</span><br /><br />";
		
		$groupSQL = "DELETE FROM " . $this->table . " WHERE id=" . $this->id;
		$groupResult = $this->conn->query($groupSQL);
		
		return $groupResult;
	}
	
	/**
	 * Loads the template object members based off the template id in the database
	 * 
	 * @param $templateId	The template to be loaded
	 */
	public function loadRecord($groupId) {
		//Set a field to use by the logger
		$this->logField = &$this->name;
		
		if(isset($groupId) && $groupId != null) {
			
			$groupSQL = "SELECT * FROM " . $this->table . " WHERE id=$groupId";
				
			$groupResult = $this->conn->query($groupSQL);

			if ($groupResult !== false && mysqli_num_rows($groupResult) > 0 )
				$row = mysqli_fetch_assoc($groupResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->name = $row['permissiongroup_name'];
				$this->edit = $row['permissiongroup_edit'];
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

		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=permissiongroup&action=read">Permission Group List</a> > <a href="admin.php?type=permissiongroup&action=update&p=' . $groupId . '">Permission Group</a><br /><br />';

		
		echo '
			<form action="admin.php?type=permissiongroup&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

			<label for="name" title="This is the group name">Group name:</label><br />
			<input name="name" id="path" type="text" maxlength="150" value="' . $this->name . '" ' . ($this->edit == 0 ? "disabled" : "") . ' />
			<div class="clear"></div>

			';
			$this->buildPermissionForm();
			
			echo '
			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($groupId) || $groupId == null) ? "Create" : "Update") . ' This Group!" /><br /><br />
			' . ((isset($groupId) && $groupId != null) ? '<a href="admin.php?type=permissiongroup&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Group!</a><br /><br />' : '') . '
			</form>
		';
	}
	
	/**
	 * Builds the various permissions form
	 * 
	 */
	private function buildPermissionForm() {
		echo "<table class='table table-bordered'>
				<tr><th>Item</th><th>Read</th><th>Insert</th><th>Update</th><th>Delete</th></tr>";
		foreach($this->availModels as $modelName) {
			$obj = new permission($this->conn, $this->log);
			$obj->setModel($modelName);
			$obj->loadRecord($this->id);
			echo '
				<tr>
					<td>' . ucfirst($modelName) . '<input type="hidden" name="' . $modelName . '_id" value="' . $obj->getId() . '" /></td>
					<td><input name="' . $modelName . '_read" id="' . $modelName . '_read" type="checkbox" value="1" '. ($obj->getRead()==1?"checked=checked":"") . ' ' . ($this->edit == 0 ? "disabled" : "") . ' /></td>
					<td><input name="' . $modelName . '_insert" id="' . $modelName . '_insert" type="checkbox" value="1" '. ($obj->getInsert()==1?"checked=checked":"") . ' ' . ($this->edit == 0 ? "disabled" : "") . ' /></td>
					<td><input name="' . $modelName . '_update" id="' . $modelName . '_update" type="checkbox" value="1" '. ($obj->getUpdate()==1?"checked=checked":"") . ' ' . ($this->edit == 0 ? "disabled" : "") . ' /></td>
					<td><input name="' . $modelName . '_delete" id="' . $modelName . '_delete" type="checkbox" value="1" '. ($obj->getDelete()==1?"checked=checked":"") . ' ' . ($this->edit == 0 ? "disabled" : "") . ' /></td>
				</tr>
				';
		}
		echo "</table>";
	}
	
	
	/**
	 * Display the template management page
	 *
	 * @param $action	The action to be performed such as update or delete
	 * @param $parent	The ID of the template object to be edited. This is the p GET Data
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
				if($user->checkPermission($this->table, 'read')) {
					$this->displayModelList();
				} else {
					echo "You do not have permissions to '<strong>read</strong>' records for " . $this->table . ".<br />";
				}
				break;
			case "insert":
				if($user->checkPermission($this->table, 'insert')) {
					//Determine if the form has been submitted
					if(isset($_POST['saveChanges'])) {
						// User has posted the article edit form: save the new article
							
						$this->storeFormValues($_POST);
							
						$result = $this->insert();
		
						//Save all the permission options
						foreach($this->permissions as $permission) {
							$permission->insert();
						}
						
						if(!$result) {
							$this->buildEditForm($parent, $child, $user);
						} else {
							$this->buildEditForm(getLastField($this->conn,$this->table, "id"), $child, $user);
							$this->log->trackChange($this->table, 'add',$user->getId(),$user->getLoginname(), $this->logField . " added");
						}
					} else {
						// User has not posted the template edit form yet: display the form
						$this->buildEditForm($parent, $child, $user);
					}
				} else {
					echo "You do not have permissions to '<strong>insert</strong>' records for " . $this->table . ".<br />";
				}
				break;
			case "update":
				if($user->checkPermission($this->table, 'update')) {
					//Determine if the form has been submitted
					if(isset($_POST['saveChanges'])) {
						// User has posted the article edit form: save the new article
		
						$this->storeFormValues($_POST);
							
						$result = $this->update($parent);
						
						//Save all the permission options
						foreach($this->permissions as $permission) {
							$permission->update();
						}
						
						//Re-build the page creation form once we are done
						$this->buildEditForm($parent, $child, $user);
		
						if($result) {
							$this->log->trackChange($this->table, 'update',$user->getId(),$user->getLoginname(), $this->logField . " updated");
						}
					} else {
						// User has not posted the template edit form yet: display the form
						$this->buildEditForm($parent, $child, $user);
					}
				} else {
					echo "You do not have permissions to '<strong>update</strong>' records for " . $this->table . ".<br />";
				}
				break;
			case "delete":
				if($user->checkPermission($this->table, 'delete')) {
					$this->storeFormValues($_POST);
					
					//Save all the permission options
					foreach($this->permissions as $permission) {
						$permission->delete();
					}
					
					$this->delete($parent);
					$ret = true;
					$this->log->trackChange($this->table, 'delete',$user->getId(),$user->getLoginname(), $this->logField . " deleted");
				} else {
					echo "You do not have permissions to '<strong>delete</strong>' records for " . $this->table . ".<br />";
				}
				break;
			default:
				echo "Error with " . $this->table . " manager<br /><br />";
		}
		return $ret;
	}
	
	/**
	 * Display the list of all permission groups
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=permissiongroup&action=read">Permission Group List</a><br /><br />';
	
		$groupSQL = "SELECT * FROM " . $this->table . " ORDER BY permissiongroup_created DESC";
		$groupResult = $this->conn->query($groupSQL);
	
		if ($groupResult !== false && mysqli_num_rows($groupResult) > 0 ) {
			while($row = mysqli_fetch_assoc($groupResult) ) {
	
				$name = stripslashes($row['permissiongroup_name']);
	
				echo "
				<div class=\"user\">
					<h2>
						<a href=\"admin.php?type=permissiongroup&action=update&p=".$row['id']."\" title=\"Edit / Manage this permission group\" alt=\"Edit / Manage this permission group\" class=\"cms_pageEditLink\" >$name</a>
							</h2>
							</div>";
			}
		} else {
			echo "
			<p>
				No permission groups found!
			</p>";
		}
	
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `templates` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `permissiongroup_name` varchar(128) DEFAULT NULL,
		  `permissiongroup_edit` tinyint(1) DEFAULT NULL,
		  `permissiongroup_created` varchar(128) DEFAULT NULL,
		
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
		
		/*Insert default data for `grouo` if we dont have one already*/
		if(countRecords($this->conn, $this->table) == 0) {
			$sql = "INSERT INTO " . $this->table . " (permissiongroup_name, permissiongroup_edit, permissiongroup_created) VALUES('Administrator', 0, '" . time() . "')";
			$this->conn->query($sql) OR DIE ("Could not insert administrator group data into \"permissiongroup\"");			
		}
		
		/*Insert default data for `permission` if we dont have one already*/
		if(countRecords($this->conn, 'permission') == false && countRecords($this->conn, $this->table) == 1 && is_array($this->availModels)) {
			foreach($this->availModels as $modelName) {
				$sql = "INSERT INTO permission (permission_groupId, permission_model, permission_read, permission_insert, permission_update, permission_delete, permission_created)
						VALUES(" . countRecords($this->conn, 'permissiongroup') . ", '" . $modelName. "', 1, 1, 1, 1, '" . time() . "')";
				$this->conn->query($sql) OR DIE ("Could not insert default permission data for \"" . $modelName . "\"");
		
			}
		}
		
	}
	
}

?>
