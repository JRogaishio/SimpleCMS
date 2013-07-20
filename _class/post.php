<?php

/**
* Class to handle articles
*/

class post
{
	// Properties
	public $id = null;
	public $pageId = null;
	//Hardcoded the author in for now
	public $authorId = 1;
	public $postDate = null;
	public $title = null;
	public $content = null;
	public $lastMod = null;
	public $constr = false;

	/**
	* Sets the object's properties using the values in the supplied array
	*
	* @param assoc The property values
	*/

	public function __construct($data=array()) {
		//Set the data to variables if the post data is set

		//I also want to do a sanitization string here. Go find my clean() function somewhere
		if(isset($data['id'])) $this->id = (int) $data['id'];
		if(isset($data['pageId'])) $this->pageId = (int) $data['pageId'];
		
		//if(isset($data['authorId'])) $this->authorId = $data['authorId'];
		
		if(isset($data['postDate'])) $this->postDate = $data['postDate'];
		if(isset($data['title'])) $this->title = $data['title'];
		if(isset($data['content'])) $this->content = $data['content'];
		if(isset($data['lastMod'])) $this->lastMod = $data['lastMod'];

		$this->constr = true;
	}


	/**
	* Sets the object's properties using the edit form post values in the supplied array
	*
	* @param assoc The form post values
	*/

	public function storeFormValues ($params) {
		// Store all the parameters
		$this->__construct($params);
	}


	/**
	* Inserts the current page object into the database, and sets its ID property.
	*/

	public function insert($pageId) {
		if($this->constr) {
			mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysql_error());
			mysql_select_db(DB_NAME) or die("Could not select database. " . mysql_error());
			
			$sql = "INSERT INTO posts (page_id, post_authorId, post_date, post_title, post_content, post_lastModified, post_created) VALUES";
			$sql .= "($this->pageId, $this->authorId, '" . date('Y-m-d H:i:s') . "', '$this->title', '$this->content', " . time() . "," . time() . ")";
			
			$result = mysql_query($sql) OR DIE ("Could not create post!");
			if($result) {
				echo "<span class='update_notice'>Created post successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load fornm data!";
		}
	}


	/**
	* Updates the current page object in the database.
	*/

	public function update($postId) {
	
		if($this->constr) {
			mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD) or die("Could not connect. " . mysql_error());
			mysql_select_db(DB_NAME) or die("Could not select database. " . mysql_error());

			$sql = "UPDATE posts SET
			page_id = '$this->pageId', 
			post_authorId = '$this->authorId', 
			post_title = '$this->title', 
			post_content = '$this->content', 
			post_lastModified = " . time() . "
			WHERE id=$postId;
			";

			$result = mysql_query($sql) OR DIE ("Could not update post!");
			if($result) {
				echo "<span class='update_notice'>Updated post successfully!</span><br /><br />";
			}

		} else {
			echo "Failed to load fornm data!";
		}

	}


	/**
	* Deletes the current page object from the database.
	*/
	public function delete($pageId, $postId) {
		//Load the post from an ID so we can say goodbye...
		$this->loadRecord($postId);
	
		echo "<span class='update_notice'>Post deleted! Bye bye '$this->title', we will miss you.</span><br /><br />";
		
		$postSQL = "DELETE FROM posts WHERE page_id=$pageId AND id=$postId";
		$postResult = mysql_query($postSQL);

	}

	public function loadRecord($postId) {
		if(isset($postId) && $postId != "new") {
			$pageSQL = "SELECT * FROM posts WHERE id=$postId";
			$pageResult = mysql_query($pageSQL);

			if ($pageResult !== false && mysql_num_rows($pageResult) > 0 )
				$row = mysql_fetch_assoc($pageResult);

			if(isset($row)) {
				$this->id = $postId;
				$this->pageId = $row['page_id'];
				$this->authorId = $row['post_authorId'];
				$this->postDate = $row['post_date'];
				$this->title = $row['post_title'];
				$this->content = $row['post_content'];
				$this->lastMod = $row['post_lastModified'];
			}
			
			$this->constr = true;
		}
	}
	
	public function buildEditForm($pageId, $postId) {
		//Load the page from an ID
		$this->loadRecord($postId);
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pageDisplay">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $pageId . '">Page</a> > <a href="admin.php?type=post&action=update&p=' . $pageId . '&c=' . $postId . '">Post</a><br /><br />';
		
		echo '<form action="admin.php?type=post&action=update&p=' . $pageId . '&c=' . $postId . '" method="post">
		<label for="pageId">Page:</label><br />';
		echo getFormattedPages("dropdown", "pageId",$pageId);
		echo '
		<br /><br /><div class="clear"></div>
		
		<label for="title">Title:</label><br />
		<input name="title" id="title" type="text" maxlength="150" value="' . $this->title . '"/>
		<div class="clear"></div>

		<label for="content">Content Text:</label><br />
		<textarea name="content" id="content" cols="60">' . $this->content . '</textarea>
		<div class="clear"></div>

		<input type="submit" name="saveChanges" class="updateBtn" value="' . ((!isset($postId) || $postId == "new") ? "Create" : "Update") . ' This Post!" /><br /><br />
		' . ((isset($postId) && $postId != "new") ? '<a href="admin.php?type=post&action=delete&p=' . $pageId . '&c=' . $postId . '" class="deleteBtn">Delete This Post!</a><br /><br />' : '') . '
		</form>
		<br />
		
		';

	}

}

?>
