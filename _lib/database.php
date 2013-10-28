<?php

function countRecords($conn, $table_name, $filters) {
	$countSQL = "SELECT * FROM $table_name " . $filters;
	$countResult = $conn->query($countSQL);
	
	return mysqli_num_rows($countResult);
}

function clean($str) {
	$cleanStr = mysql_real_escape_string($str);
	return $cleanStr;
}





?>