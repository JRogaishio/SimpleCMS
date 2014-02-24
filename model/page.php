<?php

/**
 * Class to handle pages
 *
 * @author Jacob Rogaishio
 * 
 */
class page
{
	// Properties
	public $id = null;
	public $title = null;
	public $template = null;
	public $templatePath = null;
	public $safeLink = null;
	public $metaData = null;
	public $hasBoard = null;
	public $isHome = null;
	public $constr = false;
	private $conn = null; //Database connection object
	private $linkFormat = null;
	
	/**
	 * Stores the connection object in a local variable on construction
	 *
	 * @param dbConn The property values
	 */
	public function __construct($dbConn) {
		$this->conn = $dbConn;
		$this->linkFormat = get_linkFormat($dbConn);
	}

	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['id'])) $this->id = (int) clean($this->conn, $params['id']);
		if(isset($params['title'])) $this->title = clean($this->conn, $params['title']);
		if(isset($params['template'])) $this->template = clean($this->conn, $params['template']);
		if(isset($params['safelink'])) $this->safeLink = clean($this->conn, $params['safelink']);
		if(isset($params['metadata'])) $this->metaData = clean($this->conn, $params['metadata']);
		if(isset($params['board'])) $this->hasBoard = clean($this->conn, $params['board']);
		if(isset($params['homepage'])) $this->isHome = (int) clean($this->conn, $params['homepage']);
		$this->constr = true;
	}

	
	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	private function validate() {
		$ret = "";
	
		if($this->title == "") {
			$ret = "Please enter a title.";
		} else if($this->safeLink == "") {
			$ret = "Please enter a safelink.";
		} else if(strpos($this->safeLink, " ") !== false) {
			$ret = "The safelink cannot contain any spaces.";
		} else if(preg_match("/^(SYS_)/", strtoupper($this->safeLink))) {
			$ret = "Error! Cannot create a new page with SYS_ prefix as the safe link. This is reserved for system pages!";
		}
	
		return $ret;
	}	
	
	/**
	* Inserts the current page object into the database, and sets its ID property.
	*/
	public function insert() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				//Ensure you are not submitting a system page
				if($this->isHome == 1){
					$sql = "UPDATE pages SET page_isHome=0";
					$homeResult = $this->conn->query($sql) OR DIE ("Could not update page!");
				}
				
				$sql = "INSERT INTO pages (page_template, page_safeLink, page_meta, page_title, page_hasBoard, page_isHome, page_created) VALUES";
				$sql .= "('$this->template', '$this->safeLink', '$this->metaData', '$this->title', " . convertToBit($this->hasBoard) . ", " . convertToBit($this->isHome) . "," . time() . ")";

				$result = $this->conn->query($sql) OR DIE ("Could not create page!");
				if($result) {
					echo "<span class='update_notice'>Created page successfully!</span><br /><br />";
				}
			} else {
				$ret = false;
				echo "<p class='cms_warning'>" . $error . "</p><br />";
			}
		} else {
			$ret = false;
			echo "Failed to load form data!";
		}
		return $ret;
	}

	/**
	 * Updates the current page object in the database.
	 * 
	 * @param $pageId	The page Id to update
	 * 
	 * @return returns true if the insert was successful
	 */
	public function update($pageId) {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				//Reset all home pages since we are setting a new one
				if($this->isHome == true) {
					$sql = "UPDATE pages SET page_isHome = 'false';";
					$result = $this->conn->query($sql) OR DIE ("Could not update home page!");
				}
			
				//Update the page SQL
				$sql = "UPDATE pages SET
				page_template = '$this->template', 
				page_safeLink = '$this->safeLink', 
				page_meta = '$this->metaData', 
				page_title = '$this->title', 
				page_hasBoard = '$this->hasBoard', 
				page_isHome = '$this->isHome'
				WHERE id=$pageId;
				";

				$result = $this->conn->query($sql) OR DIE ("Could not update page!");
				if($result) {
					echo "<span class='update_notice'>Updated page successfully!</span><br /><br />";
				} else {
					$ret = false;
				}
			} else {
				$ret = false;
				echo "<p class='cms_warning'>" . $error . "</p><br />";
			}

		} else {
			$ret = false;
			echo "Failed to load form data!";
		}
		return $ret;

	}

	/**
	 * Deletes the current page object from the database.
	 * 
	 * @param $pageId	The page to be deleted
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete($pageId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($pageId);
		echo "<span class='update_notice'>Page deleted! Bye bye '$this->title', we will miss you.</span><br /><br />";
		
		$pageSQL = "DELETE FROM pages WHERE id=$pageId";
		$pageResult = $this->conn->query($pageSQL);
		
		$postSQL = "DELETE FROM posts WHERE page_id=$pageId;";

		$postResult = $this->conn->query($postSQL);
	}
	
	/**
	 * Loads the page object members based off the page id in the database
	 * 
	 * @param $pageId	The page to be loaded
	 */
	public function loadRecord($pageId) {
		if(isset($pageId) && $pageId != null) {
			
			if($pageId == "home")
				$pageSQL = "SELECT * FROM pages WHERE page_isHome=true";
			else
				$pageSQL = "SELECT * FROM pages WHERE id=$pageId";
				
			$pageResult = $this->conn->query($pageSQL);

			if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 )
				$row = mysqli_fetch_assoc($pageResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->title = $row['page_title'];
				$this->template = $row['page_template'];
				$this->safeLink = $row['page_safeLink'];
				$this->metaData = $row['page_meta'];
				$this->hasBoard = $row['page_hasBoard'];
				$this->isHome = $row['page_isHome'];
			}
			
			$this->constr = true;
		}
	
	}
	
	/**
	 * Builds the admin editor form to add / update pages
	 * 
	 * @param $pageId	The page to be edited
	 */
	public function buildEditForm($pageId) {

		//Load the page from an ID
		$this->loadRecord($pageId);
		
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pageDisplay">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $this->id . '">Page</a><br /><br />';

		echo '
			<form action="admin.php?type=page&action=update&p=' . $this->id . '" method="post">

			<label for="title">Title:</label><br />
			<input name="title" id="title" type="text" class="cms_pageTextHeader" maxlength="150" value="' . $this->title . '" />
			<div class="clear"></div>
			<br />

			<label for="template">Template:</label><br />
			' . getFormattedTemplates($this->conn, "dropdown", "template",$this->template) . '
			<div class="clear"></div>
			<br />

			<label for="safelink">Safe Link:</label><br />
			<input name="safelink" id="safelink" type="text" maxlength="150" value="' . $this->safeLink . '" />
			<div class="clear"></div>
			<br />

			<label for="metadata">Meta data:</label><br />
			<input name="metadata" id="metadata" type="text" maxlength="150" value="' . $this->metaData . '" />
			<div class="clear"></div>
			<br />

			<label for="board">has Board?:</label><br />
			<input name="board" id="board" type="checkbox" value="1"'. ($this->hasBoard==1?"checked=checked":""). '/>
			<div class="clear"></div>
			<br />

			<label for="homepage">Is homepage?:</label><br />
			<input name="homepage" id="homepage" type="checkbox" value="1" '. ($this->isHome==1?"checked=checked":"") . '/>
			<div class="clear"></div>
			<br />
					
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($pageId) || $pageId == null) ? "Create" : "Update") . ' This Page!" /><br /><br />
			' . ((isset($pageId) && $pageId != null) ? '<a href="admin.php?type=page&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Page!</a><br /><br />' : '') . '
			</form>
		';
		
		if(isset($pageId) && $pageId != null)
			echo "<h2>Current Posts</h2><br />";
		
		echo $this->display_pagePosts($pageId);
		
		if(isset($pageId) && $pageId != null)
			echo "<p><a href=\"{$_SERVER['PHP_SELF']}?type=post&action=update&p=$this->id\" class=\"actionLink\">Add a New Post</a><br /></p>";

	}

	private function display_pagePosts($pageId) {
		if($pageId != null) {
			$postSQL = "SELECT * FROM posts WHERE page_id=$pageId ORDER BY post_created ASC";
			$postResult = $this->conn->query($postSQL);
			$entry_display = "";
			
			if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
				while($row = mysqli_fetch_assoc($postResult) ) {
					
					$title = stripslashes($row['post_title']);
					$postDate = stripslashes($row['post_date']);

					$entry_display .= "
					<div class=\"page\">
					<h3>
					<a href=\"admin.php?type=post&action=update&p=".$row['page_id']."&c=".$row['id']."\" title=\"Edit / Manage this post\" alt=\"Edit / Manage this page\" class=\"cms_pageEditLink\" >$title</a>
					</h3>
					<p>
					" . $postDate . "
					</p>
					<br /></div>";

				}
			} else {
				$entry_display .= "
				<p>
				No posts found!<br /><br />
				</p>";
			}
		
			return $entry_display;
		
		
		
		}
		else {
			return null;
		}
	}
	/*
	@param postLimit	The max number of posts to display on a single page
	@param showDate		True / False on whether to show the post date under the title
	@param showPerma	True / False on whether to show the permanent link to the post
	@param childId		The ID of the post to display. This is used for permalinking a swell as page scrolling using ~ and the next / back links
	@param parentId		The salfe link of the parent page. This allows you to show posts of a different page
	
	*/
	public function display_posts($postLimit, $showDate=false, $showContect=true, $showPerma=false, $childId=null, $parentLink=null) {
		if(isset($this->id)) {
			if($parentLink != null) {
				$tempId = lookupPageIdByLink($this->conn, $parentLink);
				$tempLink = $parentLink;
			}
			else {
				$tempId = $this->id;
				$tempLink = $this->safeLink;
			}
			if($postLimit == -1)
				$postSQL = "SELECT * FROM posts WHERE page_id=$tempId " . ($childId != null ? "AND id = " . clean($this->conn,$childId) : "") . " ORDER BY post_created DESC";
			else {
				if(strpos(clean($this->conn,$childId), "~") !== false) {
					$temp = str_replace("~", "", (clean($this->conn,$childId)));
					$startPos = $temp;
					
					$postSQL = "SELECT * FROM posts WHERE page_id=$tempId ORDER BY post_created DESC LIMIT $startPos, $postLimit";
				} else {
					$postSQL = "SELECT * FROM posts WHERE page_id=$tempId " . ($childId != null ? "AND id = " . $childId : "") . " ORDER BY post_created DESC LIMIT $postLimit";
				}
			}
			
			$postResult = $this->conn->query($postSQL);
			$entry_display = "";
			
			if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
				while($row = mysqli_fetch_assoc($postResult) ) {
					$postId = stripslashes($row['id']);
					$title = stripslashes($row['post_title']);
					$postDate = date(DATEFORMAT . " " . TIMEFORMAT, stripslashes($row['post_created']));
					$postContent = stripslashes($row['post_content']);

					$entry_display .= "
					<div class=\"page\">
					<h3>$title</h3>
					";
					
					if($showDate)
						$entry_display .= "<p>$postDate</p>";
					
					if($showContect) {
						$entry_display .= "					
						<p>
						$postContent
						</p>
						<br />";
					}
					if ($showPerma == true)
						$entry_display .= "Permalink: <a href='" . formatLink($this->linkFormat, $tempLink, $postId) . "'>Permalink</a><br /><br />";
						
						
					$entry_display .=  "</div>";

				}
			} else {
				$entry_display .= "
				<p>
				No posts found!
				</p>";
			}
		
			echo $entry_display;
		
		}
		else {
			return null;
		}
		
	}
	
	public function display_post_nav($postLimit, $childId) {
		if(strpos(clean($this->conn,$childId), "~") !== false) {
			$temp = str_replace("~", "", (clean($this->conn,$childId))); //Grab the current page # we are on
			if ($temp <= 0)
				$temp = 0;
				
			$startPos = $temp;
			//Calculate the number of the back limit
			$backNum = $startPos - $postLimit;
			if ($backNum <= 0)
				$backNum = 0;
			
			//Calculate how far ahead we need to go
			$nextNum = $startPos + $postLimit;
				
			echo "<a href='" . formatLink($this->linkFormat, $this->safeLink, "~" . $backNum) . "' class='cms_page_nav'>back</a> <a href='" . formatLink($this->linkFormat, $this->safeLink, "~" . $nextNum) . "' class='cms_page_nav'>next</a>";
		} else {
			echo "<a href='" . formatLink($this->linkFormat, $this->safeLink, "~0") . "' class='cms_page_nav'>back</a> <a href='" . formatLink($this->linkFormat, $this->safeLink, "~" . $postLimit) . "' class='cms_page_nav'>next</a>";
		}
		
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `pages` */
		$sql = "CREATE TABLE IF NOT EXISTS `pages` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `page_template` int(16) DEFAULT NULL,
		  `page_safeLink` varchar(32) DEFAULT NULL,
		  `page_meta` text,
		  `page_title` varchar(128) DEFAULT NULL,
		  `page_hasBoard` tinyint(1) DEFAULT NULL,
		  `page_isHome` tinyint(1) DEFAULT NULL,
		  `page_created` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"pages\"");
	}
}

?>

