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
		$data = $result->fetch(PDO::FETCH_ASSOC);
		if (is_array($data)) {
			return $data['keyValue'];
		} else {
			return null;
		}
	}	
}
