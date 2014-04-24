<?php

/**
 * Class to handle the log files
 *
 * @author Jacob Rogaishio
 * 
 */
class log extends model
{
	protected $table = "log";
	
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

