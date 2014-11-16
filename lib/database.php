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
	
	return $countResult->rowCount();
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
	//TO-DO Remove clean and use PDO parameter queries
	$str = str_replace(' ', '-', $str); // Replaces all spaces with hyphens.

   	$ret =  preg_replace('/[^A-Za-z0-9\-]/', '', $str); // Removes special chars.
   
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
	$data = $searchResult->fetchAll(PDO::FETCH_ASSOC);
	if (is_array($data)) {
		return $data;
	} else {
		return false;
	}
}

/**
 * Retuns a record set
 *
 * @param $conn			A database connection object
 * @param $table		The table to be retrieved
 * @param $col			The columns to get
 * @param $where		The where clause data
 * @param $order		The sort order in the format of FIELD ASC|DESC
 *
 * @return returns the result set
 */
function getRecords($conn, $table, $col=array(), $where=null, $order=null) {

	//Generate column list
	$colList = "";
	foreach($col as $column) {
		if($colList == "")
			$colList = $column;
		else
			$colList = "," . $column;
	}
	$whereClause = ($where != null ? "WHERE " . $where : "");
	$orderClause = ($order != null ? "ORDER BY " . $order : "");

	$sql = "SELECT " . $colList . " FROM $table $whereClause $orderClause;";

	$result = $conn->query($sql);
	$data = $result->fetch(PDO::FETCH_ASSOC);

	if (is_array($data)) {
		return $data;
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
