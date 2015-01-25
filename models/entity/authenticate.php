<?php

/**
 * Class to handle user authentication
 *
 * @author Jacob Rogaishio
 * 
 */
class authenticate extends model
{
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $login = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"login");
	protected $attemptTime= array("orm"=>true, "datatype"=>"varchar", "length"=>32, "field"=>"attemptTime");
	protected $ip = array("orm"=>true, "datatype"=>"varchar", "length"=>16, "field"=>"ip");
	
	/**
	 * Checks the IP address and returns the number of minutes you need to wait
	 *
	 */
	public function checkIP() {
		$clientIP = $_SERVER['REMOTE_ADDR'];
		
		$authSQL = "SELECT * FROM authenticate WHERE ip = :ip ORDER BY attemptTime DESC";
		$stmt = $this->conn->prepare($authSQL);
		$stmt->bindValue(':ip', $clientIP, PDO::PARAM_STR);
		$authResult = $stmt->execute();
		$attempts = $stmt->rowCount();

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

		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		//Penalty after 4 bad login attempts
		if($attempts > 3) {
			$data = $authResult->fetch(PDO::FETCH_ASSOC);
			$lastAttempt = $data[0];
			$lastTime = $data['auth_time'];
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
		$this->setLogin($userId);
		$this->setAttemptTime(time());
		$this->setIp($_SERVER['REMOTE_ADDR']);
		$this->save();
	}
	
	/**
	 * Clears all the  failed login attempts in the database
	*/
	public function clearAttempts() {
		$sql = "DELETE FROM authenticate WHERE ip=:ip;";

		$stmt = $this->_CONN->prepare($sql);
		$stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		
		$stmt->execute() OR DIE ("Could not clear authentication table!");
	}
	
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {

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
			$isToken = false;
			if(isset($post['login_username']) && isset($post['login_password'])) {
				$secPass = encrypt($post['login_password'], get_userSalt($this->conn, $post['login_username']));
				$userSQL = "SELECT * FROM account WHERE loginname=:loginname AND password=:password;";
			} else {
				$userSQL = "SELECT * FROM account WHERE token=:token;";
				$isToken = true;
			}
			$stmt = $this->conn->prepare($userSQL);
			
			if(!$isToken) {
				$stmt->bindValue(':loginname', $post['login_username'], PDO::PARAM_STR);
				$stmt->bindValue(':password', $secPass, PDO::PARAM_STR);
			} else {
				$stmt->bindValue(':token', $token, PDO::PARAM_STR);
			}
				
			$userResult = $stmt->execute();
			$userData = $stmt->fetch(PDO::FETCH_ASSOC);
			//Test to see if the auth was successful
			if (is_array($userData)) {
				$user = new account($this->conn, $this->log);

				//Set the user data
				$user->loadRecord($userData['id']);
					
				//30 minute auth time-out
				$timeout = time() + 900;
					
				$newToken = hash('sha256', (unique_salt() . $user->getLoginname()));

				$tokenSQL = "UPDATE account SET token = :token WHERE id=:id;";
				
				$stmt = $this->_CONN->prepare($tokenSQL);
				$stmt->bindValue(':token', $newToken, PDO::PARAM_STR);
				$stmt->bindValue(':id', $user->getId(), PDO::PARAM_INT);
				$tokenResult = $stmt->execute() OR DIE ("Could not update account!");

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
