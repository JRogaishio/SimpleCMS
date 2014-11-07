<?php

/**
 * Class to handle page-related requests
 *
 * @author Jacob Rogaishio
 * 
 */
class pageService extends service
{
	/**
	 * Get the oage object that is tied to this service
	 * 
	 * @return Returns the page object used by this service
	 */
	public function getPage() {
		return $this->model;
	}
	
	/**
	 * Get the post objects related to this page for the front-end
	 *
	 * @param postLimit	The max number of posts to display on a single page
	 * @param childId		The ID of the post to display. This is used for permalinking as well as page scrolling using ~ and the next / back links
	 *
	 * @return Returns array of post objects
	 */
	public function get_posts($postLimit, $childId=null) {
		$ret = array();
		
		if(isset($this->model) && $this->model->getId() != null) {
			$tempId = $this->model->getId();
				
			if($postLimit == -1) {
				$postSQL = "SELECT * FROM post WHERE pageId=$tempId ORDER BY created DESC";
			} else {
				if(strpos(clean($this->conn,$childId), "~") !== false) {
					$temp = str_replace("~", "", (clean($this->conn,$childId)));
					$startPos = $temp;
	
					$postSQL = "SELECT * FROM post WHERE pageId=$tempId ORDER BY created DESC LIMIT $startPos, $postLimit";
				} else {
					$postSQL = "SELECT * FROM post WHERE pageId=$tempId " . ($childId != null ? "AND id = " . $childId : "") . " ORDER BY created DESC LIMIT $postLimit";
				}
			}
	
			$postResult = $this->conn->query($postSQL);
			
	
			if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
				while($row = mysqli_fetch_assoc($postResult) ) {
					$postId = stripslashes($row['id']);
					$post = new post($this->conn, $this->log);
					$post->load($postId);
					array_push($ret, $post);
				}
			}
		}
		
		return $ret;
	
	}
	
	/**
	 * Display the posts related to this page for the front-end
	 * 
	 * @param postLimit	The max number of posts to display on a single page
	 * @param showDate		True / False on whether to show the post date under the title
	 * @param showPerma	True / False on whether to show the permanent link to the post
	 * @param childId		The ID of the post to display. This is used for permalinking as well as page scrolling using ~ and the next / back links
	 * @param parentId		The safe link of the parent page. This allows you to show posts of a different page
	 * 
	 * @return Returns null if no page was set
	*/
	public function display_posts($postLimit, $showDate=false, $showContent=true, $showPerma=false, $childId=null, $parentLink=null) {
		if(isset($this->model) && $this->model->getId() != null) {
			if($parentLink != null) {
				$tempId = lookupPageIdByLink($this->conn, $parentLink);
				$tempLink = $parentLink;
			}
			else {
				$tempId = $this->model->getId();
				$tempLink = $this->model->getSafeLink();
			}
			
			if($postLimit == -1) {
				$postSQL = "SELECT * FROM post WHERE pageId=$tempId " . ($childId != null ? "AND id = " . clean($this->conn,$childId) : "") . " ORDER BY created DESC";
			} else {
				if(strpos(clean($this->conn,$childId), "~") !== false) {
					$temp = str_replace("~", "", (clean($this->conn,$childId)));
					$startPos = $temp;
						
					$postSQL = "SELECT * FROM post WHERE pageId=$tempId ORDER BY created DESC LIMIT $startPos, $postLimit";
				} else {
					$postSQL = "SELECT * FROM post WHERE pageId=$tempId " . ($childId != null ? "AND id = " . $childId : "") . " ORDER BY created DESC LIMIT $postLimit";
				}
			}
				
			$postResult = $this->conn->query($postSQL);
			$entry_display = "";
				
			if ($postResult !== false && mysqli_num_rows($postResult) > 0 ) {
				while($row = mysqli_fetch_assoc($postResult) ) {
					$postId = stripslashes($row['id']);
					$title = stripslashes($row['title']);
					$postDate = date(DATEFORMAT . " " . TIMEFORMAT, stripslashes($row['created']));
					$postContent = stripslashes($row['content']);
	
					$entry_display .= "
					<div class=\"page\">
					<h3>$title</h3>
					";
						
					if($showDate)
						$entry_display .= "<p>$postDate</p>";
							
						if($showContent) {
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
	
	/**
	 * Displays a paganation for posts
	 * 
	 * @param $postLimit	An integer of the number of posts to page through
	 * @param $childId		The current page # we are on
	 */
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
	
			echo "<a href='" . formatLink($this->model->getLinkFormat(), $this->model->getSafeLink(), "~" . $backNum) . "' class='cms_page_nav'>back</a> <a href='" . formatLink($this->model->getLinkFormat(), $this->model->getSafeLink(), "~" . $nextNum) . "' class='cms_page_nav'>next</a>";
		} else {
			echo "<a href='" . formatLink($this->model->getLinkFormat(), $this->model->getSafeLink(), "~0") . "' class='cms_page_nav'>back</a> <a href='" . formatLink($this->model->getLinkFormat(), $this->model->getSafeLink(), "~" . $postLimit) . "' class='cms_page_nav'>next</a>";
		}
	
	}
	
}
