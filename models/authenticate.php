<?php

/**
 * Class to handle users
 *
 * @author Jacob Rogaishio
 * 
 */
class authenticate extends model
{

	/**
	 * Checks the IP address and returns the number of minutes you need to wait
	 *
	 */
	public function checkIP() {
		$clientIP = $_SERVER['REMOTE_ADDR'];
		
		$authSQL = "SELECT * FROM authenticate WHERE auth_ip = '" . $clientIP . "' ORDER BY auth_time DESC";
		$authResult = $this->conn->query($authSQL);
		
		$attempts = mysqli_num_rows($authResult);

		//How much to multiply the time to wait based on the failed attempts
		$multiplier = 1;
		
		//10 failed = 5 min wait. 15 failed = 10 min. 20 = 30 min, 30+ = 6hr
		if($attempts >= 30)
			$multiplier = 360;
		else if($attempts >= 20)
			$multiplier = 30;
		else if($attempts >= 15)
			$multiplier = 10;
		else if($attempts >= 5)
			$multiplier = 5;

		//Penalty after 4 bad login attempts
		if($attempts > 3) {
			$lastAttempt = mysqli_fetch_assoc($authResult);
			$lastTime = $lastAttempt['auth_time'];
			$currentTime = time();
			
			//If the last time is more than a minute old, let the authentication go through as 0 minutes waiting
			if($lastTime + (60*$multiplier) <= $currentTime) {
				return 0;
			} else {
				//Return the number of minutes you need to wait
				return $multiplier;
			}
			
		} else {
			return 0;
		}
		
		
	}
	
	
	public function logAttempt($user) {
		$sql = "INSERT INTO authenticate (auth_login, auth_time, auth_ip) VALUES";
		$sql .= "('$user', '" . time() . "', '" . $_SERVER['REMOTE_ADDR'] . "')";
		
		$result = $this->conn->query($sql) OR DIE ("Could not insert into authentication table!");
		
	}
	
	public function clearAttempts() {
		$sql = "DELETE FROM authenticate WHERE auth_ip='" . $_SERVER['REMOTE_ADDR'] . "';";
		
		$result = $this->conn->query($sql) OR DIE ("Could not clear authentication table!");
	}
	
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `users` */
		$sql = "CREATE TABLE IF NOT EXISTS `authenticate` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `auth_login` varchar(64) DEFAULT NULL,
		  `auth_time` varchar(32) DEFAULT NULL,
		  `auth_ip` varchar(16) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"authenticate\"");
	
	}
	
}

?>

