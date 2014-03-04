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
	protected $id = null;
	protected $pageId = null;
	protected $authorId = 1;
	protected $postDate = null;
	protected $title = null;
	protected $content = null;
	protected $lastMod = null;
	protected $constr = false;

	//Getters
	public function getId() {return $this->id;}
	public function getPageId() {return $this->pageId;}
	public function getAuthorId() {return $this->authorId;}
	public function getPostDate() {return $this->postDate;}
	public function getTitle() {return $this->title;}
	public function getContent() {return $this->content;}
	public function getLastMod() {return $this->lastMod;}
	public function getConstr() {return $this->constr;}
	
	//Setters
	public function setId($val) {$this->id = $val;}
	public function setPageId($val) {$this->pageId = $val;}
	public function setAuthorId($val) {$this->authorId = $val;}
	public function setPostDate($val) {$this->postDate = $val;}
	public function setTitle($val) {$this->title = $val;}
	public function setContent($val) {$this->content = $val;}
	public function setLastMod($val) {$this->lastMod = $val;}
	public function setConstr($val) {$this->constr = $val;}
	
	
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
		if(isset($params['authorId'])) $this->authorId = (int) clean($this->conn, $params['authorId']);
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
	public function update() {
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
				WHERE id=" . $this->id . ";";
				
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
	public function delete() {	
		echo "<span class='update_notice'>Post deleted! Bye bye '$this->title', we will miss you.</span><br /><br />";
		
		$postSQL = "DELETE FROM posts WHERE page_id=" . $this->pageId . " AND id=" . $this->id;

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
	public function buildEditForm($pageId, $postId, $user) {
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
		<input type="hidden" name="authorId" value="' . $user->getId() . '" />
		<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($postId) || $postId == null || $postId == "new") ? "Create" : "Update") . ' This Post!" /><br /><br />
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
		$this->loadRecord($child);
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
							$this->buildEditForm($parent, $child, $user);
						} else {
							$this->buildEditForm($parent,getLastField($this->conn,"posts", "id"), $user);
							$this->log->trackChange("post", 'add',$user->getId(),$user->getLoginname(), $this->title . " added");
						}
					}
					else {
						$result = $this->update($child);
						//Re-build the post creation form once we are done
						$this->buildEditForm($parent, $child, $user);
	
						if($result) {
							$this->log->trackChange("post", 'update',$user->getId(),$user->getLoginname(), $this->title . " updated");
						}
	
					}
	
						
				} else {
					// User has not posted the article edit form yet: display the form
					$this->buildEditForm($parent, $child, $user);
				}
				break;
			case "delete":
				//Delete the post
				$this->delete();
				$this->log->trackChange("post", 'delete',$user->getId(),$user->getLoginname(), $this->title . " deleted");
	
				//Display the page form
				$ret = true;
	
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
