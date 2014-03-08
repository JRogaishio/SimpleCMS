<?php
/**
 * Get all the pages in the system, sorted by creation descending
 * 
 * @param $conn		A database connection object
 * 
 * @return returns page select query results
 */
function getPages($conn) {
	$pageSQL = "SELECT * FROM pages ORDER BY page_created DESC";
	$pageResult = $conn->query($pageSQL);
	
	return $pageResult;
}

/**
 * Looks up the page name in the database by the page database Id
 * 
 * @param $conn		A database connection object
 * @param $pageId	The page Id to lookup
 * 
 * @return returns the page name selected
 */
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

/**
 * Looks up the page Id in the database by the page safe link
 * 
 * @param $conn		A database connection object
 * @param $pageLink	The page safe link
 * 
 * @return returns the page Id selected
 */
function lookupPageIdByLink($conn, $pageLink) {
	if($pageLink != null && $pageLink != "") {
		$pageSQL = "SELECT * FROM pages WHERE page_safeLink='$pageLink'";
	} else {
		$pageSQL = "SELECT * FROM pages WHERE page_isHome=true";
	}
	
	$pageLink = clean($conn, $pageLink);
	
	$pageResult =  $conn->query($pageSQL);
	$ret = null;
	if(mysqli_num_rows($pageResult) > 0) {
		$row = mysqli_fetch_assoc($pageResult);
		$ret = $row['id'];
	}
	
	return $ret;
}

/**
 * Generate a list of all the pages in a certain format
 * 
 * @param $conn			A database connection object
 * @param $format		The particular format you want to export in. "dropdown" is the only supported format currently
 * @param $eleName		The HTML element name
 * @param $defaultVal	The HTML default value
 * 
 * @return returns a formatted HTML page list
 */
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

/**
 * Looks up a template name by the database Id
 * 
 * @param $conn			A database connection object
 * @param $templateId	The template database name
 * 
 * @return returns the template database name
 */
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

/**
 * Generate a list of all templates in a certain format
 * 
 * @param $conn			A database connection object
 * @param $format		The particular format you want to export in. "dropdown" is the only supported format currently
 * @param $eleName		The HTML element name
 * @param $defaultVal	The HTML default value
 * 
 * @return returns a formatted HTML template list
 */
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

/**
 * Looks up a users password salt in the database
 * 
 * @param $conn		A database connection object
 * @param $username	A users name in the database
 * 
 * @return returns the users salt if they exist or false if the user does not exist
 */
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

/**
 * Gets the link format from the database
 * 
 * @param $conn		A database connection object
 * 
 * @return returns the link format per the database
 */
function get_linkFormat($conn) {

	$siteSQL = "SELECT * FROM sites;";
	$siteResult =  $conn->query($siteSQL);

	if ($siteResult !== false && mysqli_num_rows($siteResult) > 0 ) {
		$siteData = mysqli_fetch_assoc($siteResult);
		return $siteData['site_linkFormat'];
	} else {
		return false;
	}

}

/**
 * Generates the link per the supplied format
 * 
 * @param $format	A link format
 * @param $p		The parent link
 * @param $c		The child link
 *
 * @return returns the formatted link
 */
function formatLink($format, $p, $c=null) {
	$ret = "";

	switch($format) {
		case "raw":
			$ret = "?p=" . $p . ($c != "" && $c != null ? "&c=" . $c : "");
			return $ret;
			break;
		case "clean":
			$ret = SITE_ROOT . "page/" . $p . "/" . ($c != "" && $c != null ? "article/" . $c : "");
			return $ret;
			break;
		default:
			return false;
	}
}

/**
 * Generates the correct error code page based off the link provided
 * 
 * @param $code	A link format
 *
 * @return returns the formatted link
 */
function loadErrorPage($code = "SYS_404") {
	$ret = ERROR_DIR;
	
	//Determine if we just have a bad page or a system error
	if(strpos($code,"SYS_") !== false) {
		$code = str_replace("SYS_", "", $code); //Clean the code string
		
		switch($code) {
			case "400":
				$ret .= "400.php";
				break;
			case "401":
				$ret .= "401.php";
				break;
			case "403":
				$ret .= "403.php";
				break;
			case "404":
				$ret .= "404.php";
				break;
			case "418":
				$ret .= "418.php";
				break;
			case "500":
				$ret .= "500.php";
				break;
			default:
				$ret .= "ERROR.php";
				break;
		}
	} else {
		$ret .= "404.php";
	}
	return $ret;
}

?>

