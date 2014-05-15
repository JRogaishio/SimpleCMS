<?php

/**
 * Class to handle key-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class keyService extends service
{
	
	/**
	 * Get the key value from the database
	 *
	 * @param $key	The key name in the database
	 *
	 * @return Returns the key value if found otherwise null
	 */
	public function getValue($key) {
		$sql = "SELECT * FROM customkey WHERE keyItem='$key';";
		$result =  $this->conn->query($sql);
	
		if ($result !== false && mysqli_num_rows($result) > 0 ) {
			$data = mysqli_fetch_assoc($result);
			return $data['keyValue'];
		} else {
			return null;
		}
	}	
}
