<?php

/**
 * Class to handle pages
 *
 * @author Jacob Rogaishio
 * 
 */
class page extends model
{
	// Properties
	protected $table = "page";
	protected $id = null;
	protected $title = null;
	protected $template = null;
	protected $templatePath = null;
	protected $safeLink = null;
	protected $metaData = null;
	protected $flags = null;
	protected $isHome = null;
	protected $constr = false;

	//Getters
	public function getId() {return $this->id;}
	public function getTitle() {return $this->title;}
	public function getTemplate() {return $this->template;}
	public function getTemplatePath() {return $this->templatePath;}
	public function getSafeLink() {return $this->safeLink;}
	public function getMetaData() {return $this->metaData;}
	public function getFlags() {return explode(",", $this->flags);}
	public function getIsHome() {return $this->isHome;}
	public function getConstr() {return $this->constr;}

	//Setters
	public function setId($val) {$this->id = $val;}
	public function setTitle($val) {$this->title = $val;}
	public function setTemplate($val) {$this->template = $val;}
	public function setTemplatePath($val) {$this->templatePath = $val;}
	public function setSafeLink($val) {$this->safeLink = $val;}
	public function setMetaData($val) {$this->metaData = $val;}
	public function setFlags($arr=array()) {$this->flags = implode(",", $arr);}
	public function setIsHome($val) {$this->isHome = $val;}
	public function setConstr($val) {$this->constr = $val;}
	
	/**
	 * Returns true if the flag exists or false if it doesnt
	 * 
	 * @param $flag	The flag to check if it exists
	 * 
	 * @return Returns true if exists or false if doesn't
	 */
	public function hasFlag($flag) {
		$ret = in_array($flag, explode(",", $this->flags));
		
		return $ret;
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
		if(isset($params['flags'])) $this->flags = clean($this->conn, $params['flags']);
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
	* 
	* @return Returns true on insert success
	*/
	public function insert() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				//Ensure you are not submitting a system page
				if($this->isHome == 1){
					$sql = "UPDATE " . $this->table . " SET page_isHome=0";
					$homeResult = $this->conn->query($sql) OR DIE ("Could not update home page!");
				}
				
				$sql = "INSERT INTO " . $this->table . " (page_template, page_safeLink, page_meta, page_title, page_flags, page_isHome, page_created) VALUES";
				$sql .= "('$this->template', '$this->safeLink', '$this->metaData', '$this->title', '$this->flags', " . convertToBit($this->isHome) . "," . time() . ")";

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
	 * @return returns true if the update was successful
	 */
	public function update() {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				//Reset all home pages since we are setting a new one
				if($this->isHome == true) {
					$sql = "UPDATE " . $this->table . " SET page_isHome = false;";
					$result = $this->conn->query($sql) OR DIE ("Could not update home page!");
				}
			
				//Update the page SQL
				$sql = "UPDATE " . $this->table . " SET
				page_template = '$this->template', 
				page_safeLink = '$this->safeLink', 
				page_meta = '$this->metaData', 
				page_title = '$this->title', 
				page_flags = '$this->flags', 
				page_isHome = " . convertToBit($this->isHome) . "
				WHERE id=" . $this->id . ";";

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
	 */
	public function delete() {
		echo "<span class='update_notice'>Page deleted! Bye bye '$this->title', we will miss you.</span><br /><br />";
		
		$pageSQL = "DELETE FROM " . $this->table . " WHERE id=" . $this->id;
		$pageResult = $this->conn->query($pageSQL);
		
		$postSQL = "DELETE FROM post WHERE page_id=" . $this->id;
		$postResult = $this->conn->query($postSQL);
	}
	
	/**
	 * Loads the page object members based off the page id in the database
	 */
	public function loadRecord($pageId) {
		//Set a field to use by the logger
		$this->logField = &$this->title;
		
		if(isset($pageId) && $pageId != null) {
			
			if($pageId == "home")
				$pageSQL = "SELECT * FROM " . $this->table . " WHERE page_isHome=true";
			else
				$pageSQL = "SELECT * FROM " . $this->table . " WHERE id=$pageId";
				
			$pageResult = $this->conn->query($pageSQL);

			if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 )
				$row = mysqli_fetch_assoc($pageResult);

			if(isset($row)) {
				$this->id = $row['id'];
				$this->title = $row['page_title'];
				$this->template = $row['page_template'];
				$this->safeLink = $row['page_safeLink'];
				$this->metaData = $row['page_meta'];
				$this->flags = $row['page_flags'];
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
	public function buildEditForm($pageId, $child=null, $user=null) {

		//Load the page from an ID
		$this->loadRecord($pageId);
		
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pageDisplay">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $this->id . '">Page</a><br /><br />';

		echo '
			<form action="admin.php?type=page&action=' . (($this->id == null) ? "insert" : "update") . '&p=' . $this->id . '" method="post">

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

			<label for="flags">Flags (separated by comma):</label><br />
			<input name="flags" id="flags" type="text" value="' . $this->flags . '" />
			<div class="clear"></div>
			<br />

			<label for="homepage">Is homepage?:</label><br />
			<input name="homepage" id="homepage" type="checkbox" value="1" '. ($this->isHome==1?"checked=checked":"") . '/>
			<div class="clear"></div>
			<br />
					
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($pageId) || $pageId == null) ? "Create" : "Update") . ' This Page!" /><br /><br />
			' . ((isset($pageId) && $pageId != null) ? '<a href="admin.php?type=page&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Page!</a><br /><br />' : '') . '
			</form>
		';
		
		if(isset($pageId) && $pageId != null)
			echo "<h2>Current Posts</h2><br />";
		
		echo $this->display_pagePosts($pageId);
		
		if(isset($pageId) && $pageId != null)
			echo "<p><a href=\"{$_SERVER['PHP_SELF']}?type=post&action=update&p=$this->id\" class=\"actionLink\">Add a New Post</a><br /></p>";

	}

	/**
	 * Loads all posts related to a page Id
	 *
	 * @param $pageId	The pageId used in posts
	 */
	private function display_pagePosts($pageId) {
		if($pageId != null) {
			$postSQL = "SELECT * FROM post WHERE page_id=$pageId ORDER BY post_created ASC";
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
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `pages` */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->table . "` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `page_template` int(16) DEFAULT NULL,
		  `page_safeLink` varchar(32) DEFAULT NULL,
		  `page_meta` text,
		  `page_title` varchar(128) DEFAULT NULL,
		  `page_flags` text,
		  `page_isHome` tinyint(1) DEFAULT NULL,
		  `page_created` varchar(128) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"" . $this->table . "\"");
	}
}

?>

