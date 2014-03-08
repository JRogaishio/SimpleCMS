<?php
/*
Board class
Author: Twitch2641
Written: 2013-03-28

Overview:
This class takes 1 parameter (The post ID) when created.
This class then builds all the comments to that particular post and all subcomments.
I am using recursive functions to build an "indented" format of comments and not a simple list.
*/

class board {

	private $conn;
	private $log;
	private $controller;
	
	private $pageId;
	
	public function __construct($conn, $log, $controller) {
		$this->conn = $conn;
		$this->log = $log;
		$this->controller = $controller;
		
		$this->buildTable();
	}
	
	/*
	Initializer function! Woo!
	*/
	public function loadBoard() {	
		$this->pageId = lookupPageIdByLink($this->conn, $this->controller->get_PARENT());
		
		if(isset($_POST['type']) && isset($_POST['action'])) {
			if($_POST['type'] == "board" && $_POST['action'] == "post") {
				$sql = "INSERT INTO board (board_postId, board_authorId, board_comment, board_replyTo, board_datePosted, board_lastUpdated) VALUES";
				$sql .= "('" . $this->pageId . "', '1', '" . $_POST['board_message'] . "', NULL, '" . date("Y-m-d H:i:s") . "', NULL)";
				
				$result = $this->conn->query($sql) OR DIE ("Could not create board post!");
				
				echo "Inserted post into board";
			}
		}		
		
		

		$this->displayPostForm(null);
		
		//Load the board
		$this->loadComments();
		
	}

	private function displayPostForm($replyToId) {
		echo "<form action='{$_SERVER['PHP_SELF']}?p=" . $this->controller->get_PARENT() . "' method='post'>
				<input type='hidden' name='type' value='board' />
				<input type='hidden' name='action' value='post' />
				<input type='hidden' name='reply' value='' />
				";
		echo "<textarea name='board_message'></textarea><br />
				<input type='submit' name='board_submit' value='Submit post!' />
		";
		echo "</form>";
	
	}
	
	/*
	The below function loads all top level comments. This funtion also starts a recursive call to the subComments
	function to load all subcomments
	*/
	private function loadComments() {
		$result = mysql_query("SELECT * FROM board WHERE board_postID=" . $this->pageId . " AND board_replyTo IS NULL;");
	
		if($result !== false && mysql_num_rows($result) > 0) {
			//Loop through all the row
			while($row = mysql_fetch_array($result))
			{	
				echo "<div class='comment_Main'>";
				$this->generateComment($row);
				echo "</div>";
			}
		} else {
			echo "No posts found!";
		}
	}
	
	/*
	The loadSubComments function recursively calls itself to load all subcomments under a parent ID.
	This function stops recursively running once all the comments have been loaded
	*/
	private function loadSubComments($parentID) {
		$result = mysql_query("SELECT * FROM board WHERE board_postID=" . $this->pageId . " AND board_replyTo=" . $parentID . ";");
	
		//Loop through all the row
		while($row = mysql_fetch_array($result))
		{
			echo "<div class='comment_Sub'>";
			$this->generateComment($row);
			echo "</div>";
		}
	}
	
	/*
	The below function contains all the comment data.
	This function allows me to easily update the comment layout between the main and recursive functions
	*/
	private function generateComment($data) {
		//Loop through all the row
		echo $data['board_comment'] . " <br /> Posted By: " . $data['board_authorId'];
		echo "<br />";
		$this->loadSubComments($data['id']);
		
	}
	
	
	public function buildTable() {
		/*Table structure for table `pages` */
		$sql = "CREATE TABLE IF NOT EXISTS `pages` (
		  `id` int(16) NOT NULL AUTO_INCREMENT,
		  `board_postId` int(16) DEFAULT NULL,
		  `board_authorId` int(16) DEFAULT NULL,
		  `board_comment` text,
		  `board_replyTo` int(16) DEFAULT NULL,
		  `board_datePosted` datetime,
		  `board_lastUpdated` datetime,
		  PRIMARY KEY (`id`)
		)";
		$this->conn->query($sql) OR DIE ("Could not build table \"board\"");
	}
	
	
}

?>