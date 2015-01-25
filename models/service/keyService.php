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
		$sql = "SELECT * FROM customkey WHERE keyItem=:keyItem;";
		
		$stmt = $this->_CONN->prepare($sql);
		$stmt->bindValue(':keyItem', $key, PDO::PARAM_STR);
		$stmt->execute();
		
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

		if (is_array($data)) {
			return $data['keyValue'];
		} else {
			return null;
		}
	}	
}
