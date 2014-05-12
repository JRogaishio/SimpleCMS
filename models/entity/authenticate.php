<?php

/**
 * Class to handle users
 *
 * @author Jacob Rogaishio
 * 
 */
class authenticate extends model
{
	// Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $login = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"login");
	protected $attemptTime= array("orm"=>true, "datatype"=>"varchar", "length"=>32, "field"=>"attemptTime");
	protected $ip = array("orm"=>true, "datatype"=>"varchar", "length"=>16, "field"=>"ip");
	
	//Getters
	/*public function getId() {return $this->get($this->id);}
	public function getLogin() {return $this->get($this->login);}
	public function getAttemptTime() {return $this->get($this->attemptTime);}
	public function getIp() {return $this->get($this->ip);}
	
	//Setters
	public function setId($val) {$this->set($this->id, $val);}
	public function setLogin($val) {$this->set($this->login, $val);}
	public function setAttemptTime($val) {$this->set($this->attemptTime, $val);}
	public function setIp($val) {$this->set($this->ip, $val);}*/
	
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
	
	/**
	 * Logs the failed login attempt in the database
	 * 
	 * @param $userId	The user ID that tried to login
	 */
	public function logAttempt($userId) {
		$sql = "INSERT INTO authenticate (auth_login, auth_time, auth_ip) VALUES";
		$sql .= "('$userId', '" . time() . "', '" . $_SERVER['REMOTE_ADDR'] . "')";
		
		$result = $this->conn->query($sql) OR DIE ("Could not insert into authentication table!");
		
	}
	
	/**
	 * Clears all the  failed login attempts in the database
	*/
	public function clearAttempts() {
		$sql = "DELETE FROM authenticate WHERE auth_ip='" . $_SERVER['REMOTE_ADDR'] . "';";
		
		$result = $this->conn->query($sql) OR DIE ("Could not clear authentication table!");
	}
	
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `authenticate` */
		$sql = "CREATE TABLE IF NOT EXISTS `authenticate` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `auth_login` varchar(64) DEFAULT NULL,
		  `auth_time` varchar(32) DEFAULT NULL,
		  `auth_ip` varchar(16) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"authenticate\"");
	
	}
	
	/**
	 * Function to authenticate the user against the DB
	 *
	 * @param $post		POST data for the current request
	 * @param $token	An encrypted random string used for cookies and saved sessions
	 *
	 * @return Returns either an authenticated user or null on authentication failure
	 *
	 */
	public function authUser($post, $token) {
		//Check to see if any login info was posted or if a token exists
		if((($token!=null) || (isset($post['login_username']) && isset($post['login_password']))) && countRecords($this->conn,"account") > 0) {
			if(isset($post['login_username']) && isset($post['login_password'])) {
				$secPass = encrypt(clean($this->conn,$post['login_password']), get_userSalt($this->conn, clean($this->conn, $post['login_username'])));

				$userSQL = "SELECT * FROM account WHERE account_login='" . clean($this->conn,$post['login_username']) . "' AND account_pass='$secPass';";
			} else {
				$userSQL = "SELECT * FROM account WHERE account_token='$token';";
			}

			$userResult = $this->conn->query($userSQL);

			//Test to see if the auth was successful
			if ($userResult !== false && mysqli_num_rows($userResult) > 0 ) {
				$userData = mysqli_fetch_assoc($userResult);

				$user = new account($this->conn, $this->log);

				//Set the user data
				$user->loadRecord($userData['id']);
					
				//30 minute auth time-out
				$timeout = time() + 900;
					
				$newToken = hash('sha256', (unique_salt() . $user->getLoginname()));

				$tokenSQL = "UPDATE account SET account_token = '$newToken' WHERE id=" . $user->getId() . ";";
				$tokenResult = $this->conn->query($tokenSQL) OR DIE ("Could not update account!");
				if(!$tokenResult) {
					echo "<span class='update_notice'>Failed to update login token!</span><br /><br />";
				}
					
				//Create a random cookie based off of the user name and a unique salt
				setcookie("token", $newToken, $timeout);
					
				//Log that a user logged in. POST data is only set on the initial login
				if(isset($post['login_username']) && isset($post['login_password'])) {
					$this->log->trackChange("account", 'log_in',$user->getId(),$user->getLoginname(), "logged in");
				}
					
				//Clear out the failed authentications
				$this->clearAttempts();
					
				return $user;
					
			} else {
					
				$this->log->trackChange("account", 'log_in',null, clean($this->conn,(isset($post['login_username']) ? $post['login_username'] : null)), "FAILED LOGIN");
					
				//Log the failed authentications
				$this->logAttempt((isset($post['login_username']) ? $post['login_username'] : null));
					
				return null;
			}
		}//Token / Postdata set validation
	}
}

?>
