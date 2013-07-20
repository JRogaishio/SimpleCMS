<?php

function getPages() {
	$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
	$pageResult = mysql_query($pageSQL);
	
	return $pageResult;
}

function lookupPageNameById($pageId) {
	$pageSQL = "SELECT * FROM pages WHERE id=$pageId";
	$pageResult = mysql_query($pageSQL);
	$name = null;
	
	if(mysql_num_rows($pageResult) > 0) {
		$row = mysql_fetch_assoc($pageResult);
		$name = $row['page_title'];
	}
	
	return $name;
}


function getFormattedPages($format, $eleName, $defaultVal) {
	$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
	$pageResult = mysql_query($pageSQL);
	$formattedData = "";
	
	if ($pageResult !== false && mysql_num_rows($pageResult) > 0 ) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";
				
				if($defaultVal != null && $defaultVal != "new")
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupPageNameById($defaultVal) . "--</option>";
				
				while($row = mysql_fetch_assoc($pageResult) ) {
					$formattedData .= "<option value='" . stripslashes($row['id']) . "'>" . stripslashes($row['page_title']) . "</option>";
				}
				$formattedData .= "</select>";
				
				break;
		} //End switch
		
		//Return the formated data
		return $formattedData;
	} else {
		return false;
	}
}

function lookupTemplateNameById($templateId) {
	$templateSQL = "SELECT * FROM templates WHERE id=$templateId";
	$templateResult = mysql_query($templateSQL);
	$name = null;
	
	if(mysql_num_rows($templateResult) > 0) {
		$row = mysql_fetch_assoc($templateResult);
		$name = $row['template_name'];
	}
	
	return $name;
}

function countRecords($table_name, $filters) {
	$countSQL = "SELECT * FROM $table_name " . $filters;
	$countResult = mysql_query($countSQL);
	
	return mysql_num_rows($countResult);
}

function getFormattedTemplates($format, $eleName, $defaultVal) {
	$templateSQL = "SELECT * FROM templates ORDER BY template_created DESC";
	$templateResult = mysql_query($templateSQL);
	$formattedData = "";
	
	if ($templateResult !== false && mysql_num_rows($templateResult) > 0 ) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";
				
				if($defaultVal != null)
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupTemplateNameById($defaultVal) . "--</option>";
				
				while($row = mysql_fetch_assoc($templateResult) ) {
					$formattedData .= "<option value='" . stripslashes($row['id']) . "'>" . stripslashes($row['template_name']) . "</option>";
				}
				$formattedData .= "</select>";
				
				break;
		} //End switch
		
		//Return the formated data
		return $formattedData;
	} else {
		return false;
	}
	
}

function clean($str) {
	$cleanStr = mysql_real_escape_string($str);
	return $cleanStr;
}



function unique_salt() {
    mt_srand(microtime(true)*100000 + memory_get_usage(true));
    return hash('sha256', (uniqid(mt_rand(), true)));
}

	
function get_userSalt($username) {
	$userSQL = "SELECT * FROM users WHERE user_login='$username';";
	$userResult = mysql_query($userSQL);

	if ($userResult !== false && mysql_num_rows($userResult) > 0 ) {
		$userData = mysql_fetch_assoc($userResult);
		return $userData['user_salt'];
	} else {
		return false;
	}
}

?>