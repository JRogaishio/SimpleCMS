<?php

/**
 * Class to handle the log files
 *
 * @author Jacob Rogaishio
 * 
 */
class log extends model
{
	
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
	function trackChange($type, $action, $userId, $user, $change) {
	
		$sql = "INSERT INTO " . $this->table . " (log_type, log_action, log_accountId, log_user, log_info, log_date, log_created, log_remoteIp) VALUES";
		$sql .= "('$type', '$action', '$userId', '$user', '$change', '" . date('Y-m-d H:i:s') . "','" . time() . "','" . $_SERVER['REMOTE_ADDR'] . "')";
	
		$result = $this->conn->query($sql) OR DIE ("Could not write to log!");
	
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
		$resultList = "";
		$logSQL = "SELECT * FROM " . $this->table . " ORDER BY log_created DESC;";
		$logResult = $this->conn->query($logSQL);
	
		if ($logResult !== false && mysqli_num_rows($logResult) > 0 ) {
			$resultList .= "
			<h3>Results in log:</h3>
			<br /><br />
			<table class='table table-bordered'>
			<tr><th>User</th><th>Type</th><th>Details</th><th>Date</th><th>IP Address</th></tr>
			";
			while($row = mysqli_fetch_assoc($logResult))
				$resultList .= "<tr><td>" . $row['log_user'] . "</td><td>" . $row['log_type'] . "</td><td>" . $row['log_info'] . "</td><td>" . $row['log_date'] . "</td><td>". $row['log_remoteIp'] . "</td></tr>";
				
			$resultList .= "</table>";
				
			echo $resultList;
				
		} else {
			echo "No logs found?";
		}
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `log` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `log_type` varchar(64) DEFAULT NULL,
		  `log_action` varchar(64) DEFAULT NULL,
		  `log_accountId` varchar(64) DEFAULT NULL,
		  `log_user` varchar(64) DEFAULT NULL,
		  `log_info` text,
		  `log_date` datetime DEFAULT NULL,
		  `log_created` varchar(128) DEFAULT NULL,
		  `log_remoteIp` varchar(64) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
		
	}
}

?>

