<?php

/**
 * Class to handle the basic model operations and properties
 *
 * @author Jacob Rogaishio
 *
 */
class model extends orm {
	//Non-persistant properties
	protected $conn = null; //Database connection object
	protected $log = null;
	protected $linkFormat = null;
	protected $logField = null;
	
	/**
	 * Stores the connection object in a local variable on construction
	 *
	 * @param $dbConn	The property values
	 * @param $dbLog	The log object used by the system
	 */
	public function __construct($dbConn, $dbLog, $surpressLinkCheck=false) {
		$this->conn = $dbConn;
		$this->table = get_class($this);
		$this->log = $dbLog;
		if(!$surpressLinkCheck)
			$this->linkFormat = get_linkFormat($dbConn);
		$this->logField = null; //Default to the Id by ref incase it changes from a load
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
		$this->loadRecord($parent, $child);
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

						if(!$result) {
							$this->buildEditForm($parent, $child, $user);
						} else {
							if($parent == null)
								$parent = getLastField($this->conn,$this->table, "id");
							else if($child == null)
								$child = getLastField($this->conn,$this->table, "id");
								
							$this->buildEditForm($parent, $child, $user);
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
						
						$result = $this->update();
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
					$this->remove();
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
	 * Inserts the current object into the database
	 */
	public function insert($surpressNotify = false) {
		$error = $this->validate();
		if($error == "") {	
			$this->setCreated(time());
			
			$this->preSave();
			$ret = $this->save();
			if($ret && !$surpressNotify) {
				echo "<span class='update_notice'>Created " . $this->table . " successfully!</span><br /><br />";
			}
		}  else {
			$ret = false;
			echo "<p class='cms_warning'>" . $error . "</p><br />";
		}
		
		return $ret;
	}
	
	/**
	 * Updates the current object in the database.
	 */
	public function update($surpressNotify = false) {		
		$error = $this->validate();

		if($error == "") {
			$this->preSave();
			$ret = $this->save();
			if($ret && !$surpressNotify) {
				echo "<span class='update_notice'>Updated " . $this->table . " successfully!</span><br /><br />";
			}
		} else {
			$ret = false;
			echo "<p class='cms_warning'>" . $error . "</p><br />";
		}
		return $ret;
	}
	
	/**
	 * Deletes the current object from the database.
	 *
	 * @return returns the database result on the delete query
	 */
	public function remove($surpressNotify = false) {
		$this->preDelete();
		$result = $this->delete();
	
		if(!$surpressNotify) {
			echo "<span class='update_notice'>" . ucfirst($this->table) . " deleted! Bye bye '$this->logField', we will miss you.</span><br /><br />";
		}
		return $result;
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
	 * Any pre-formatting before save opperatins
	 *
	 * @return Returns true or false based on pre saving success
	 */
	protected function preSave() {
		return true;
	}
	
	/**
	 * Any pre-formatting before delete opperatins
	 *
	 * @return Returns true or false based on pre delete success
	 */
	protected function preDelete() {
		return true;
	}
}

?>
