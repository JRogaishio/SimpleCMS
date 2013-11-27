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
	
	return mysqli_num_rows($countResult);
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





?>

