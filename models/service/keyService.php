<?php

/**
 * Class to handle page-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class keyService extends service
{
	
	public function getValue($key) {
		$sql = "SELECT * FROM customkeys WHERE key_name='$key';";
		$result =  $this->conn->query($sql);
	
		if ($result !== false && mysqli_num_rows($result) > 0 ) {
			$data = mysqli_fetch_assoc($result);
			return $data['key_value'];
		} else {
			return null;
		}
	}	
}
