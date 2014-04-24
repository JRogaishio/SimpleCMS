<?php

class model {
	
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
	public function __construct($dbConn, $dbLog) {
		$this->conn = $dbConn;
		$this->log = $dbLog;
		$this->linkFormat = get_linkFormat($dbConn);
		$this->logField = &$this->id; //Default to the Id
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
			case "insert":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					
					$this->storeFormValues($_POST);
					
					$result = $this->insert();
				
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
				break;
			case "update":
				//Determine if the form has been submitted
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
						
					$this->storeFormValues($_POST);
					
					$result = $this->update($parent);
					//Re-build the page creation form once we are done
					$this->buildEditForm($parent, $child, $user);

					if($result) {
						$this->log->trackChange($this->table, 'update',$user->getId(),$user->getLoginname(), $this->logField . " updated");
					}
				} else {
					// User has not posted the template edit form yet: display the form
					$this->buildEditForm($parent, $child, $user);
				}
				break;
			case "delete":
				$this->delete($parent);
				$ret = true;
				$this->log->trackChange($this->table, 'delete',$user->getId(),$user->getLoginname(), $this->logField . " deleted");
				break;
			default:
				echo "Error with " . $this->table . " manager<br /><br />";
				$ret = true;
		}
		return $ret;
	}
}

?>
