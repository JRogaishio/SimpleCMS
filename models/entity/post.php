<?php

/**
 * Class to handle articles attached to pages
 *
 * @author Jacob Rogaishio
 * 
 */
class post extends model
{
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $pageId = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"pageId");
	protected $authorId = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"authorId");
	protected $title = array("orm"=>true, "datatype"=>"varchar", "length"=>150, "field"=>"title");
	protected $content = array("orm"=>true, "datatype"=>"text", "field"=>"content");
	protected $lastModified = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"lastModified");
	protected $createdDate = array("orm"=>true, "datatype"=>"datetime", "field"=>"createdDate");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");	
	
	/**
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		// Store all the parameters
		//Set the data to variables if the post data is set
		if(isset($params['id'])) $this->setId(clean($this->conn, $params['id']));
		if(isset($params['pageId'])) $this->setPageId(clean($this->conn, $params['pageId']));
		if(isset($params['authorId'])) $this->setAuthorId(clean($this->conn, $params['authorId']));
		if(isset($params['postDate'])) $this->setPostDate(clean($this->conn, $params['postDate']));
		if(isset($params['title'])) $this->setTitle(clean($this->conn, $params['title']));
		if(isset($params['content'])) $this->setContent(clean($this->conn, $params['content']));
		if(isset($params['lastMod'])) $this->setLastModified(clean($this->conn, $params['lastMod']));
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
	
		if($this->title == "") {
			$ret = "Please enter a title.";
		} else if($this->content == "") {
			$ret = "Please enter content.";
		}
	
		return $ret;
	}
	
	/**
	 * Any pre-formatting before save opperatins
	 *
	 * @return Returns true or false based on pre saving success
	 */
	protected function preSave() {
		$ret = false;
		
		//On insert, set the created datetime
		if($this->getCreatedDate() == null) {
			$this->setCreatedDate(date('Y-m-d H:i:s'));
		}
		
		$this->setLastModified(time());
		
		return $ret;
	}
	
	/**
	 * Loads the post object members based off the post id in the database
	 * 
	 * @param $postId	The post to be loaded
	 */
	public function loadRecord($pageId, $postId) {
		if(isset($postId) && $postId != null) {
			$this->load($postId);
						
			//Set a field to use by the logger
			$this->logField = $this->getTitle();
		}
	}
	
	/**
	 * Builds the admin editor form to add / update posts
	 * 
	 * @param $pageId	The page this post is tied to
	 * @param $postId	The post to be edited
	 */
	public function buildEditForm($pageId, $postId, $user) {
		//Load the page from an ID
		$this->loadRecord($pageId, $postId);
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=page&action=read">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $pageId . '">Page</a> > <a href="admin.php?type=post&action=update&p=' . $pageId . '&c=' . $postId . '">Post</a><br /><br />';
		
		echo '<form action="admin.php?type=post&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $pageId . '&c=' . $postId . '" method="post">
		<label for="pageId">Page:</label><br />';
		echo getFormattedPages($this->conn, "dropdown", "pageId",$this->getPageId());
		echo '
		<div class="clear"></div>
		<br />
		
		<label for="title">Title:</label><br />
		<input name="title" id="title" type="text" maxlength="150" value="' . $this->getTitle() . '"/>
		<div class="clear"></div>
		<br />

		<label for="content">Content Text:</label><br />
		<textarea name="content" id="content" cols="60">' . $this->getContent() . '</textarea>
		<div class="clear"></div>
		<br />
		<input type="hidden" name="authorId" value="' . $user->getId() . '" />
		<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($postId) || $postId == null || $postId == "new") ? "Create" : "Update") . ' This Post!" /><br /><br />
		' . ((isset($postId) && $postId != null) ? '<a href="admin.php?type=post&action=delete&p=' . $pageId . '&c=' . $postId . '" class="deleteBtn">Delete This Post!</a><br /><br />' : '') . '
		</form>
		<br />
		
		';

	}

	/**
	 * Display the list of all posts and their respective pages
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=post&action=read">Post List</a><br /><br />';
	
		$postSQL = "SELECT * FROM " . $this->table . " ORDER BY pageId ASC";
		$postResult = $this->conn->query($postSQL);
		$lastPageName = "";
	
		if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
			while($row = mysqli_fetch_assoc($postResult) ) {
	
				if($lastPageName != lookupPageNameById($this->conn, $row['id'])) {
					//If we aren't on the first page in the list, add some line breaks inbetween page lists.
					if($lastPageName != "")
						echo "<br /><br />";
						
					$lastPageName = lookupPageNameById($this->conn, $row['id']);
					echo "<h1 class='cms_pageTitle'>" . $lastPageName . "</h1>";
				}
	
				$title = stripslashes($row['title']);
				$postDate = stripslashes($row['createdDate']);
	
				echo "
				<div class=\"page\">
				<h3>
				<a href=\"admin.php?type=post&action=update&p=".$row['pageId']."&c=".$row['id']."\" title=\"Edit / Manage this post\" alt=\"Edit / Manage this page\" class=\"cms_pageEditLink\" >$title</a>
					</h3>
					<p>
					" . $postDate . "
				</p>
				</div>";
	
			}
		} else {
			echo "
			<p>
			No posts found!<br /><br />
			</p>";
		}
	}
}

?>
