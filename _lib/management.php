<?php
function getPages($conn) {
	$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
	$pageResult = $conn->query($pageSQL);
	
	return $pageResult;
}

function lookupPageNameById($conn, $pageId) {
	$pageSQL = "SELECT * FROM pages WHERE id=$pageId";
	$pageResult =  $conn->query($pageSQL);
	$name = null;
	if(mysqli_num_rows($pageResult) > 0) {
		$row = mysqli_fetch_assoc($pageResult);
		$name = $row['page_title'];
	}
	
	return $name;
}


function getFormattedPages($conn, $format, $eleName, $defaultVal) {

	$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
	$pageResult =  $conn->query($pageSQL);
	$formattedData = "";
	if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 ) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";
				
				if($defaultVal != null && $defaultVal != "new")
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupPageNameById($conn, $defaultVal) . "--</option>";
				
				while($row = mysqli_fetch_assoc($pageResult) ) {
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

function lookupTemplateNameById($conn, $templateId) {

	$templateSQL = "SELECT * FROM templates WHERE id=$templateId";
	$templateResult =  $conn->query($templateSQL);
	$name = null;
	
	if(mysqli_num_rows($templateResult) > 0) {
		$row = mysqli_fetch_assoc($templateResult);
		$name = $row['template_name'];
	}

	return $name;
}

function getFormattedTemplates($conn, $format, $eleName, $defaultVal) {

	$templateSQL = "SELECT * FROM templates ORDER BY template_created DESC";
	$templateResult =  $conn->query($templateSQL);
	$formattedData = "";

	if ($templateResult !== false && mysqli_num_rows($templateResult) > 0 ) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";
				
				if($defaultVal != null)
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupTemplateNameById($conn, $defaultVal) . "--</option>";
				
				while($row = mysqli_fetch_assoc($templateResult) ) {
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

function get_userSalt($conn, $username) {

	$userSQL = "SELECT * FROM users WHERE user_login='$username';";
	$userResult =  $conn->query($userSQL);

	if ($userResult !== false && mysqli_num_rows($userResult) > 0 ) {
		$userData = mysqli_fetch_assoc($userResult);
		return $userData['user_salt'];
	} else {
		return false;
	}

}

function logChange($conn, $type, $action, $userId, $user, $change) {

	$sql = "INSERT INTO log (log_type, log_action, log_userId, log_user, log_info, log_date, log_created, log_remoteIp) VALUES";
	$sql .= "('$type', '$action', '$userId', '$user', '$change', '" . date('Y-m-d H:i:s') . "','" . time() . "','" . $_SERVER['REMOTE_ADDR'] . "')";

	$result = $conn->query($sql) OR DIE ("Could not write to log!");
	
	return $result;	
}






?>