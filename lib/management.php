<?php
/**
 * Get all the pages in the system, sorted by creation descending
 * 
 * @param $conn		A database connection object
 * 
 * @return returns page select query results
 */
function getPages($conn) {
	$pageSQL = "SELECT * FROM page ORDER BY page_created DESC";
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
	$pageSQL = "SELECT * FROM page WHERE id=$pageId";
	$pageResult =  $conn->query($pageSQL);
	$row = $pageResult->fetch(PDO::FETCH_ASSOC);
	$name = null;
	if(is_array($row)) {
		$name = $row['title'];
	}
	
	return $name;
}

/**
 * Looks up the group name in the database by the group database Id
 *
 * @param $conn		A database connection object
 * @param $groupId	The permission group Id to lookup
 *
 * @return returns the page name selected
 */
function lookupGroupNameById($conn, $groupId) {
	$groupSQL = "SELECT title FROM permissiongroup WHERE id=$groupId";
	$groupResult =  $conn->query($groupSQL);
	$row = $groupResult->fetch(PDO::FETCH_ASSOC);
	$name = null;
	if(is_array($row)) {
		$name = $row['title'];
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
	if($pageLink != null && $pageLink != "")
		$pageSQL = "SELECT * FROM page WHERE page_safeLink=:pageLink'";
	else
		$pageSQL = "SELECT * FROM page WHERE page_isHome=true";
	
	$stmt = $conn->prepare($pageSQL);
	
	//Bind the safelink if there is one
	if($pageLink != null && $pageLink != "")
		$stmt->bindValue(':' . $col[$i], $pageLink, PDO::PARAM_STR);
	
	$pageResult = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	$ret = null;
	if(is_array($row)) {
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

	$pageSQL = "SELECT * FROM page ORDER BY created DESC";
	$pageResult =  $conn->query($pageSQL);
	$data = $pageResult->fetchAll(PDO::FETCH_ASSOC);
	$formattedData = "";
	if (is_array($data)) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";
				
				if($defaultVal != null && $defaultVal != "new")
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupPageNameById($conn, $defaultVal) . "--</option>";
				
				foreach($data as $row) {
					$formattedData .= "<option value='" . stripslashes($row['id']) . "'>" . stripslashes($row['title']) . "</option>";
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
 * Generate a list of all the groups in a certain format
 *
 * @param $conn			A database connection object
 * @param $format		The particular format you want to export in. "dropdown" is the only supported format currently
 * @param $eleName		The HTML element name
 * @param $defaultVal	The HTML default value
 *
 * @return returns a formatted HTML page list
 */
function getFormattedGroups($conn, $format, $eleName, $defaultVal) {

	$groupSQL = "SELECT * FROM permissiongroup ORDER BY created DESC";
	$groupResult =  $conn->query($groupSQL);
	$data = $groupResult->fetchAll(PDO::FETCH_ASSOC);
	$formattedData = "";
	if (is_array($data)) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";

				if($defaultVal != null && $defaultVal != "new")
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupGroupNameById($conn, $defaultVal) . "--</option>";

				foreach($data as $row) {
					$formattedData .= "<option value='" . stripslashes($row['id']) . "'>" . stripslashes($row['title']) . "</option>";
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

	$templateSQL = "SELECT * FROM template WHERE id=$templateId";
	$templateResult =  $conn->query($templateSQL);
	$row = $templateResult->fetch(PDO::FETCH_ASSOC);
	$name = null;
	
	if(is_array($row)) {
		$name = $row['title'];
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

	$templateSQL = "SELECT * FROM template ORDER BY created DESC";
	$templateResult =  $conn->query($templateSQL);
	$formattedData = "";
	$rows = $templateResult->fetchAll(PDO::FETCH_ASSOC);
	if (is_array($rows)) {
		switch ($format) {
			case "dropdown":
				$formattedData = "<select name='" . $eleName . "'>";
				
				if($defaultVal != null)
					$formattedData .=  "<option selected value='" . $defaultVal . "'>--" . lookupTemplateNameById($conn, $defaultVal) . "--</option>";
				
				foreach ($rows as $row) {
					$formattedData .= "<option value='" . stripslashes($row['id']) . "'>" . stripslashes($row['title']) . "</option>";
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
 * @param $conn			A database connection object
 * @param $username		A users name in the database
 * 
 * @return returns the users salt if they exist or false if the user does not exist
 */
function get_userSalt($conn, $username) {

	$userSQL = "SELECT salt FROM account WHERE loginname=:loginname;";
	$stmt = $conn->prepare($userSQL);
	$stmt->bindValue(':loginname', $username, PDO::PARAM_STR);
	$result = $stmt->execute();
	
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if (is_array($row)) {
		return $row['salt'];
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

	$siteSQL = "SELECT urlFormat FROM site;";
	$stmt = $conn->query($siteSQL);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if (is_array($row))
		return $row['urlFormat'];
	else
		return false;
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

/**
 * Print out arrays for debugging
 * @param mixed $arr
 */
function debug($arr) {
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
	exit;
}

?>
