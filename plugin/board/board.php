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
	private $scope;
	
	private $_postID;
	
	public function __construct($conn, $log, $scope) {
		$this->conn = $conn;
		$this->log = $log;
		$this->scope = $scope;
	}
	
	/*
	Initializer function! Woo!
	*/
	public function loadBoard($postID) {
		$this->_postID = $postID;
		
		$this->displayPostForm(null);
		
		//Load the board
		$this->loadComments();
	}

	private function displayPostForm($replyToId) {
		echo "<form action='{$_SERVER['PHP_SELF']}?type=board&action=post&p=$replyToId' method='post'>";
		echo "<textarea name='board_message'></textarea>
				<input type='submit' name='board_submit' value='Submit post!' />
		";
		echo "</form>";
	
	}
	
	/*
	The below function loads all top level comments. This funtion also starts a recursive call to the subComments
	function to load all subcomments
	*/
	private function loadComments() {
		$result = mysql_query("SELECT * FROM board WHERE board_postID=" . $this->_postID . " AND board_replyTo IS NULL;");
	
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
		$result = mysql_query("SELECT * FROM board WHERE board_postID=" . $this->_postID . " AND board_replyTo=" . $parentID . ";");
	
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
}

?>