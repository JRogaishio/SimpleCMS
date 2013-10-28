<?php

/**
* Class to handle articles
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
	
	/**
	* Sets the object's properties using the values in the supplied array
	*
	* @param assoc The property values
	*/
	public function __construct($dbConn) {
		$this->conn = $dbConn;
	}

	/**
	* Sets the object's properties using the edit form post values in the supplied array
	*
	* @param assoc The form post values
	*/
	public function storeFormValues ($params) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($params['id'])) $this->id = (int) $params['id'];
		if(isset($params['title'])) $this->title = $params['title'];
		if(isset($params['template'])) $this->template = $params['template'];
		if(isset($params['safelink'])) $this->safeLink = $params['safelink'];
		if(isset($params['metadata'])) $this->metaData = $params['metadata'];
		if(isset($params['board'])) $this->hasBoard = $params['board'];
		if(isset($params['homepage'])) $this->isHome = (int) $params['homepage'];
		$this->constr = true;
	}

	/**
	* Inserts the current page object into the database, and sets its ID property.
	*/
	public function insert() {
		if($this->constr) {

			if($this->isHome == 1){
				$sql = "UPDATE pages SET page_isHome=0";
				$homeResult = $this->conn->query($sql) OR DIE ("Could not update page!");
			}
			
			$sql = "INSERT INTO pages (page_template, page_safeLink, page_meta, page_title, page_hasBoard, page_isHome, page_created) VALUES";
			$sql .= "('$this->template', '$this->safeLink', '$this->metaData', '$this->title', '$this->hasBoard', '$this->isHome'," . time() . ")";

			$result = $this->conn->query($sql) OR DIE ("Could not create page!");
			if($result) {
				echo "<span class='update_notice'>Created page successfully!</span><br /><br />";
			}
			

		} else {
			echo "Failed to load fornm data!";
		}
	}

	/**
	* Updates the current page object in the database.
	*/
	public function update($pageId) {
	
		if($this->constr) {

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
			}

		} else {
			echo "Failed to load fornm data!";
		}

	}

	/**
	* Deletes the current page object from the database.
	*/
	public function delete($pageId) {
		//Load the page from an ID so we can say goodbye...
		$this->loadRecord($pageId);
		echo "<span class='update_notice'>Post deleted! Bye bye '$this->title', we will miss you.</span><br /><br />";
		
		$pageSQL = "DELETE FROM pages WHERE id=$pageId";
		$pageResult = $this->conn->query($pageSQL);
		
		$postSQL = "DELETE FROM posts WHERE page_id=$pageId;";

		$postResult = $this->conn->query($postSQL);
	}
	
	public function loadRecord($pageId) {
		if(isset($pageId) && $pageId != "new") {
			
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
	
	public function buildEditForm($pageId) {

		//Load the page from an ID
		$this->loadRecord($pageId);
		
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pageDisplay">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $this->id . '">Page</a><br /><br />';

		echo '
			<form action="admin.php?type=page&action=update&p=' . $this->id . '" method="post">

			<label for="title">Title:</label><br />
			<input name="title" id="title" type="text" class="cms_pageTextHeader" maxlength="150" value="' . $this->title . '" />
			<div class="clear"></div>

			<label for="template">Template:</label><br />
			' . getFormattedTemplates($this->conn, "dropdown", "template",$this->template) . '
			<br /><br /><div class="clear"></div>

			<label for="safelink">Safe Link:</label><br />
			<input name="safelink" id="safelink" type="text" maxlength="150" value="' . $this->safeLink . '" />
			<div class="clear"></div>

			<label for="metadata">Meta data:</label><br />
			<input name="metadata" id="metadata" type="text" maxlength="150" value="' . $this->metaData . '" />
			<div class="clear"></div>

			<label for="board">has Board?:</label><br />
			<input name="board" id="board" type="checkbox" value="1"'. ($this->hasBoard==1?"checked=checked":""). '/>
			<div class="clear"></div>

			<label for="homepage">Is homepage?:</label><br />
			<input name="homepage" id="homepage" type="checkbox" value="1" '. ($this->isHome==1?"checked=checked":"") . '/>

			<div class="clear"></div>
			<br />
			<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($pageId) || $pageId == "new") ? "Create" : "Update") . ' This Page!" /><br /><br />
			' . ((isset($pageId) && $pageId != "new") ? '<a href="admin.php?type=page&action=delete&p=' . $this->id . '"" class="deleteBtn">Delete This Page!</a><br /><br />' : '') . '
			</form>
		';
		
		if(isset($pageId) && $pageId != "new")
			echo "<h2>Current Posts</h2><br />";
		
		echo $this->display_pagePosts($pageId);
		
		if(isset($pageId) && $pageId != "new")
			echo "<p><a href=\"{$_SERVER['PHP_SELF']}?type=post&action=update&p=$this->id\" class=\"actionLink\">Add a New Post</a><br /></p>";

	}

	private function display_pagePosts($pageId) {
		if($pageId != "new" && $pageId != null) {
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
	
	public function display_posts($postLimit, $showDate) {
	
		if(isset($this->id)) {
			if($postLimit == -1)
				$postSQL = "SELECT * FROM posts WHERE page_id=$this->id ORDER BY post_created ASC";
			else
				$postSQL = "SELECT * FROM posts WHERE page_id=$this->id ORDER BY post_created ASC LIMIT $postLimit";
				
			$postResult = $this->conn->query($postSQL);
			$entry_display = "";
			
			if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
				while($row = mysqli_fetch_assoc($postResult) ) {
					
					$title = stripslashes($row['post_title']);
					$postDate = stripslashes($row['post_date']);
					$postContent = stripslashes($row['post_content']);

					$entry_display .= "
					<div class=\"page\">
					<h3>$title</h3>
					";
					
					if($showDate)
						$entry_display .= "<p>$postDate</p>";
					
					$entry_display .= "					
					<p>
					$postContent
					</p>
					<br /><br />
					</div>";

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
	
	
}

?>
