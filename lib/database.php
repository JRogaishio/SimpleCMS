<?php

/**
 * Counts the number of records in a supplied table
 * 
 * @param $conn			A database connection object
 * @param $table_name	The table to be counted
 * @param $filters		Any SQL WHERE filters that need to be added
 
 * @return returns the number of rows in the table provided
 */
function countRecords($conn, $table_name, $filters = "") {
	$countSQL = "SELECT * FROM $table_name " . $filters;
	$countResult = $conn->query($countSQL);
	
	//Return false if the tables doesn't exist
	if(!$countResult) {
		return false;
	} else {
		return mysqli_num_rows($countResult) OR DIE("ERROR");
	}
}
/**
 * Sanitizes user input using the mysqli_real_escape_string method
 * 
 * @param $conn			A database connection object
 * @param $str			The string that needs to be sanitized
 *
 * @return returns the sanitized string
 */
function clean($conn, $str) {
	$ret = mysqli_real_escape_string($conn, $str);
	return $ret;
}


/**
 * Searches a set list of fields for a value in the SQL database
 * 
 * @param $conn			A database connection object
 * @param $search		The value being searched for
 * @param $table		The table to be searched
 * @param $col			The columns to search
 *
 * @return returns the result set
 */
function searchTable($conn, $search, $table, $col=array()) {
	$searchCols = "";

	for($i=0; $i < count($col); $i++) {
		if($i>0)
			$searchCols .= " OR ";
			
		$searchCols .= $col[$i] . " LIKE '%" . $search . "%'";
	}
	
	$searchSQL = "SELECT * FROM $table WHERE $searchCols;";
	$searchResult = $conn->query($searchSQL);

	if ($searchResult !== false && mysqli_num_rows($searchResult) > 0 ) {
		return $searchResult;
	} else {
		return false;
	}
}

/**
 * Retuns a bit value from a string
 *
 * @param $str	A string to be converted
 *
 * @return returns the bit value of 1 or 0
 */
function convertToBit($str) {
	$ret = null;

	switch(strtoupper($str)) {
		case "":
			$ret = 0;
			break;
		case "0":
			$ret = 0;
			break;
		case "1":
			$ret = 1;
			break;
		case "TRUE":
			$ret = 1;
			break;
		case "FALSE":
			$ret = 0;
			break;
	}
	
	return $ret;
}

/**
 * Returns a specific field from the last record in a table
 *
 * @param $conn			A database connection object
 * @param $table_name	The table to be searched
 * @param $field		The field to pull

 * @return returns the number of rows in the table provided
 */
function getLastField($conn, $table_name, $field) {
	$searchSQL = "SELECT $field FROM $table_name ORDER BY id DESC;";
	$searchResult = $conn->query($searchSQL);

	$ret = "";
	
	if(mysqli_num_rows($searchResult) > 0) {
		$searchData = mysqli_fetch_assoc($searchResult);
		$ret = $searchData[$field];
	}
	
	return $ret;
}

?>
