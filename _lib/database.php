<?php

function countRecords($table_name, $filters) {
	$countSQL = "SELECT * FROM $table_name " . $filters;
	$countResult = mysql_query($countSQL);
	
	return mysql_num_rows($countResult);
}

function clean($str) {
	$cleanStr = mysql_real_escape_string($str);
	return $cleanStr;
}





?>