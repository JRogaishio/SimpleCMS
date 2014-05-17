<?php

/**
 * Class to handle the log files
 *
 * @author Jacob Rogaishio
 * 
 */
class log extends model
{
	// Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $model = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"model");
	protected $action = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"action");
	protected $accountId = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"accountId");
	protected $loginname = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"loginname");
	protected $info = array("orm"=>true, "datatype"=>"text", "field"=>"info");
	protected $actionDate = array("orm"=>true, "datatype"=>"datetime", "field"=>"actionDate");
	protected $remoteIp = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"remoteIp");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
	
	/**
	 * Inserts any changes in the log database table
	 *
	 * @param $type		The type of changed performed. Ex. user, page
	 * @param $action	The action performed on the change type. Ex. log_out, log_in, add, remove
	 * @param $userId	The user Id who performed the change
	 * @param $user		The The user name who performed the change
	 * @param $change	A more detailed description of the change made. Ex. "Page Blog added"
	 *
	 * @return returns the result of the mysql query
	 */
	function trackChange($model, $action, $userId, $user, $change) {
		$result = null;
		
		$this->setModel($model);
		$this->setAction($action);
		$this->setAccountId($userId);
		$this->setLoginname($user);
		$this->setInfo($change);
		$this->setActionDate(date('Y-m-d H:i:s'));
		$this->setRemoteIp($_SERVER['REMOTE_ADDR']);
		$this->setCreated(time());
		$result = $this->save();

		return $result;
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
		$ret = false;
		switch($action) {
			case "read":
				if($user->checkPermission($this->table, 'read', false)) {
					$this->displayModelList();
				} else {
					echo "You do not have permissions to '<strong>read</strong>' records for " . $this->table . ".<br />";
				}
				break;
			case "insert":
				//Nothing to do here
				break;
			case "update":
				//Nothing to do here
				break;
			case "delete":
				//Nothing to do here
				break;
			default:
				echo "Error with " . $this->table . " manager<br /><br />";
		}
		return $ret;
	}
	
	
	/**
	 * Display the system log
	 *
	 */
	public function displayModelList() {		
		$logList = $this->loadList(new log($this->conn, $this->log), "created:DESC");
		
		if (count($logList)) {
			echo "<h3>Results in log:</h3><br /><br />
			<table class='table table-bordered'>
			<tr><th>User</th><th>Type</th><th>Details</th><th>Date</th><th>IP Address</th></tr>";
			
			foreach($logList as $logItem) {
				echo "<tr><td>" . $logItem->getLoginname() . "</td><td>" . $logItem->getModel() . "</td><td>" . $logItem->getInfo() . "</td><td>" . $logItem->getActionDate() . "</td><td>". $logItem->getRemoteIp() . "</td></tr>";
			}
			
			echo "</table>";
		} else {
			echo "No logs found?";
		}
	}
}

?>

