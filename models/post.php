<?php

/**
 * Class to handle articles attached to pages
 *
 * @author Jacob Rogaishio
 * 
 */
class post extends model
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
	 * Sets the object's properties using the edit form post values in the supplied array
	 *
	 * @param params The form post values
	 */
	public function storeFormValues ($params) {
		// Store all the parameters
		//Set the data to variables if the post data is set

		if(isset($params['id'])) $this->id = (int) clean($this->conn, $params['id']);
		if(isset($params['pageId'])) $this->pageId = (int) clean($this->conn, $params['pageId']);
		if(isset($params['postDate'])) $this->postDate = clean($this->conn, $params['postDate']);
		if(isset($params['title'])) $this->title = clean($this->conn, $params['title']);
		if(isset($params['content'])) $this->content = clean($this->conn, $params['content']);
		if(isset($params['lastMod'])) $this->lastMod = clean($this->conn, $params['lastMod']);

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
		} else if($this->content == "") {
			$ret = "Please enter content.";
		}
	
		return $ret;
	}
	
	
	/**
	 * Inserts the current post object into the database
	 * 
	 * @param $pageId	The page this post is tied to
	 */
	public function insert($pageId) {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$sql = "INSERT INTO posts (page_id, post_authorId, post_date, post_title, post_content, post_lastModified, post_created) VALUES";
				$sql .= "($this->pageId, $this->authorId, '" . date('Y-m-d H:i:s') . "', '$this->title', '$this->content', " . time() . "," . time() . ")";
				
				$result = $this->conn->query($sql) OR DIE ("Could not create post!");
				if($result) {
					echo "<span class='update_notice'>Created post successfully!</span><br /><br />";
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
	 * Updates the current post object in the database.
	 * 
	 * @param $postId	The post Id to update
	 */
	public function update($postId) {
		$ret = true;
		if($this->constr) {
			$error = $this->validate();
			if($error == "") {
				$sql = "UPDATE posts SET
				page_id = '$this->pageId', 
				post_authorId = '$this->authorId', 
				post_title = '$this->title', 
				post_content = '$this->content', 
				post_lastModified = " . time() . "
				WHERE id=$postId;
				";
	
				$result = $this->conn->query($sql) OR DIE ("Could not update post!");
				if($result) {
					echo "<span class='update_notice'>Updated post successfully!</span><br /><br />";
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
	 * Deletes the current post object from the database.
	 * 
	 * @param $pageId	The page this post is tied to
	 * @param $postId	The post to be deleted
	 * 
	 * @return returns the database result on the delete query
	 */
	public function delete($pageId, $postId) {
		//Load the post from an ID so we can say goodbye...
		$this->loadRecord($postId);
	
		echo "<span class='update_notice'>Post deleted! Bye bye '$this->title', we will miss you.</span><br /><br />";
		
		$postSQL = "DELETE FROM posts WHERE page_id=$pageId AND id=$postId";
		$postResult = $this->conn->query($postSQL);
		
		return $postResult;
	}

	/**
	 * Loads the post object members based off the post id in the database
	 * 
	 * @param $postId	The post to be loaded
	 */
	public function loadRecord($postId) {
		if(isset($postId) && $postId != null) {
			$pageSQL = "SELECT * FROM posts WHERE id=$postId";
			$pageResult = $this->conn->query($pageSQL);

			if ($pageResult !== false && mysqli_num_rows($pageResult) > 0 )
				$row = mysqli_fetch_assoc($pageResult);

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
	
	/**
	 * Builds the admin editor form to add / update posts
	 * 
	 * @param $pageId	The page this post is tied to
	 * @param $postId	The post to be edited
	 */
	public function buildEditForm($pageId, $postId) {
		//Load the page from an ID
		$this->loadRecord($postId);
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=pageDisplay">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $pageId . '">Page</a> > <a href="admin.php?type=post&action=update&p=' . $pageId . '&c=' . $postId . '">Post</a><br /><br />';
		
		echo '<form action="admin.php?type=post&action=update&p=' . $pageId . '&c=' . $postId . '" method="post">
		<label for="pageId">Page:</label><br />';
		echo getFormattedPages($this->conn, "dropdown", "pageId",$pageId);
		echo '
		<div class="clear"></div>
		<br />
		
		<label for="title">Title:</label><br />
		<input name="title" id="title" type="text" maxlength="150" value="' . $this->title . '"/>
		<div class="clear"></div>
		<br />

		<label for="content">Content Text:</label><br />
		<textarea name="content" id="content" cols="60">' . $this->content . '</textarea>
		<div class="clear"></div>
		<br />

		<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($postId) || $postId == null) ? "Create" : "Update") . ' This Post!" /><br /><br />
		' . ((isset($postId) && $postId != null) ? '<a href="admin.php?type=post&action=delete&p=' . $pageId . '&c=' . $postId . '" class="deleteBtn">Delete This Post!</a><br /><br />' : '') . '
		</form>
		<br />
		
		';

	}

	/**
	 * Display the post management page
	 *
	 */
	public function displayManager($action, $parent, $child, $user, $auth=null) {
		$ret = false;
		switch($action) {
			case "update":
				if(isset($_POST['saveChanges'])) {
					// User has posted the article edit form: save the new article
					$this->storeFormValues($_POST);
						
					if($child==null) {
						$result = $this->insert($parent);
						if(!$result) {
							//Re-build the post creation form once we are done
							$this->buildEditForm($parent, $child);
						} else {
							$this->buildEditForm($parent,getLastField($this->conn,"posts", "id"));
							$this->log->trackChange("post", 'add',$user->id,$user->loginname, $this->title . " added");
						}
					}
					else {
						$result = $this->update($child);
						//Re-build the post creation form once we are done
						$this->buildEditForm($parent, $child);
	
						if($result) {
							$this->log->trackChange("post", 'update',$user->id,$user->loginname, $this->title . " updated");
						}
	
					}
	
						
				} else {
					// User has not posted the article edit form yet: display the form
					$this->buildEditForm($parent, $child);
				}
				break;
			case "delete":
				//Delete the post
				$this->delete($parent, $child);
				$this->log->trackChange("post", 'delete',$user->id,$user->loginname, $this->title . " deleted");
	
				//Display the page form
				$page = new Page($this->conn);
				parent::addToScope($page);
				$page->buildEditForm($parent);
	
				break;
			default:
				echo "Error with post manager<br /><br />";
				$ret = true;
		}
		return $ret;
	}
	
	/**
	 * Builds the necessary tables for this object
	 *
	 */
	public function buildTable() {
		/*Table structure for table `posts` */
		$sql = "CREATE TABLE IF NOT EXISTS `posts` (
			  `id` int(16) NOT NULL AUTO_INCREMENT,
			  `page_id` int(16) DEFAULT NULL,
			  `post_authorId` int(16) DEFAULT NULL,
			  `post_date` datetime DEFAULT NULL,
			  `post_title` varchar(150) DEFAULT NULL,
			  `post_content` text,
			  `post_lastModified` VARCHAR(100) DEFAULT NULL,
			  `post_created` VARCHAR(128) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			)";
		$this->conn->query($sql) OR DIE ("Could not build table \"posts\"");
	}
}

?>


