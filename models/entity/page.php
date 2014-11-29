<?php

/**
 * Class to handle pages
 *
 * @author Jacob Rogaishio
 * 
 */
class page extends model
{	
	//Persistant Properties
	protected $id = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"id", "primary"=>true);
	protected $title = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"title");
	protected $templateId = array("orm"=>true, "datatype"=>"int", "length"=>16, "field"=>"templateId");
	protected $safeLink = array("orm"=>true, "datatype"=>"varchar", "length"=>64, "field"=>"safeLink");
	protected $metaData = array("orm"=>true, "datatype"=>"text", "field"=>"metaData");
	protected $flags = array("orm"=>true, "datatype"=>"text", "field"=>"flags");
	protected $isHome = array("orm"=>true, "datatype"=>"tinyint", "length"=>1, "field"=>"isHome");
	protected $created = array("orm"=>true, "datatype"=>"varchar", "length"=>128, "field"=>"created");
		
	//Non-Persistant properties
	protected $templatePath = null;
	
	//Getters
	public function getTemplatePath() {return $this->templatePath;}
	
	//Setters
	public function setTemplatePath($val) {$this->templatePath = $val;}
	
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
		if(isset($params['id'])) $this->setId(clean($this->conn, $params['id']));
		if(isset($params['title'])) $this->setTitle(clean($this->conn, $params['title']));
		if(isset($params['template'])) $this->setTemplateId(clean($this->conn, $params['template']));
		if(isset($params['safelink'])) $this->setSafeLink(clean($this->conn, $params['safelink']));
		if(isset($params['metadata'])) $this->setMetaData(clean($this->conn, $params['metadata']));
		if(isset($params['flags'])) $this->setFlags(clean($this->conn, $params['flags']));
		if(isset($params['homepage'])) $this->setIsHome(clean($this->conn, $params['homepage']));
	}

	/**
	 * validate the fields
	 *
	 * @return Returns true or false based on validation checks
	 */
	protected function validate() {
		$ret = "";
	
		if($this->getTitle() == "") {
			$ret = "Please enter a title.";
		} else if($this->getSafeLink() == "") {
			$ret = "Please enter a safelink.";
		} else if(strpos($this->getSafeLink(), " ") !== false) {
			$ret = "The safelink cannot contain any spaces.";
		} else if(preg_match("/^(SYS_)/", strtoupper($this->getSafeLink()))) {
			$ret = "Error! Cannot create a new page with SYS_ prefix as the safe link. This is reserved for system pages!";
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

		//Set all other pages to be not a homepage
		if($this->getIsHome() === 1){
			$sql = "UPDATE " . $this->table . " SET isHome=0";
			$ret = $this->conn->query($sql) OR DIE ("Could not update home page!");
		}
		
		return $ret;
	}
	
	/**
	 * Any pre-formatting before save opperatins
	 *
	 * @return Returns true or false based on pre saving success
	 */
	protected function preDelete() {
		$ret = false;
		$postSQL = "DELETE FROM post WHERE pageId=" . $this->getId();
		$ret = $this->conn->query($postSQL);
		
		return $ret;
	}
	
	/**
	 * Loads the page object members based off the page id in the database
	 */
	public function loadRecord($p=null, $c=null) {
		if(isset($p) && $p != null) {
			
			if($p == "home")
				$pageSQL = "SELECT * FROM " . $this->table . " WHERE isHome=true";
			else
				$pageSQL = "SELECT * FROM " . $this->table . " WHERE id=$p";
			
			$pageResult = $this->conn->query($pageSQL);
			$row = $pageResult->fetch(PDO::FETCH_ASSOC);

			if(is_array($row)) {
				$this->load($row['id']);

				//Set a field to use by the logger
				$this->logField = $this->getTitle();
			}
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
		
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=page&action=read">Page List</a> > <a href="admin.php?type=page&action=update&p=' . $this->getId() . '">Page</a><br /><br />';

		echo '
			<form action="admin.php?type=page&action=' . (($this->getId() == null) ? "insert" : "update") . '&p=' . $this->getId() . '" method="post">

			<label for="title">Title:</label><br />
			<input name="title" id="title" type="text" class="cms_pageTextHeader" maxlength="150" value="' . $this->getTitle() . '" />
			<div class="clear"></div>
			<br />

			<label for="template">Template:</label><br />
			' . getFormattedTemplates($this->conn, "dropdown", "template",$this->getTemplateId()) . '
			<div class="clear"></div>
			<br />

			<label for="safelink">Safe Link:</label><br />
			<input name="safelink" id="safelink" type="text" maxlength="150" value="' . $this->getSafeLink() . '" />
			<div class="clear"></div>
			<br />

			<label for="metadata">Meta data:</label><br />
			<input name="metadata" id="metadata" type="text" maxlength="150" value="' . $this->getMetaData() . '" />
			<div class="clear"></div>
			<br />

			<label for="flags">Flags (separated by comma):</label><br />
			<input name="flags" id="flags" type="text" value="' . $this->getFlags() . '" />
			<div class="clear"></div>
			<br />

			<label for="homepage">Is homepage?:</label><br />
			<input name="homepage" id="homepage" type="checkbox" value="1" '. ($this->getIsHome()==1?"checked=checked":"") . '/>
			<div class="clear"></div>
			<br />
					
			<input type="submit" name="saveChanges" class="btn btn-success btn-large" value="' . ((!isset($pageId) || $pageId == null) ? "Create" : "Update") . ' This Page!" /><br /><br />
			' . ((isset($pageId) && $pageId != null) ? '<a href="admin.php?type=page&action=delete&p=' . $this->getId() . '"" class="deleteBtn">Delete This Page!</a><br /><br />' : '') . '
			</form>
		';

		if(isset($pageId) && $pageId != null)
			echo "<h2>Current Posts</h2><br />";
		
		echo $this->display_pagePosts($pageId);
		
		if(isset($pageId) && $pageId != null)
			echo "<p><a href=\"{$_SERVER['PHP_SELF']}?type=post&action=insert&p=" . $this->getId() . "\" class=\"actionLink\">Add a New Post</a><br /></p>";

	}

	/**
	 * Loads all posts related to a page Id
	 *
	 * @param $pageId	The pageId used in posts
	 */
	private function display_pagePosts($pageId) {
		if($pageId != null) {
			$postList = $this->loadArr(new post($this->conn, $this->log), "created:ASC", array("pageId = $pageId"));
			$entry_display = "";
			
			if (count($postList)) {
				foreach($postList as $post) {
					$entry_display .= "
					<div class=\"page\">
					<h3>
					<a href=\"admin.php?type=post&action=update&p=".$post->getPageId()."&c=".$post->getId()."\" title=\"Edit / Manage this post\" alt=\"Edit / Manage this page\" class=\"cms_pageEditLink\" >" . $post->getTitle() . "</a>
					</h3>
					<p>
					" . $post->getCreatedDate() . "
					</p>
					<br /></div>";
				}
			} else {
				echo "No posts found!<br /><br />";
			}
		
			return $entry_display;
		}
		else {
			return null;
		}
	}
	
	/**
	 * Display the list of all pages
	 *
	 */
	public function displayModelList() {
		echo '<a href="admin.php">Home</a> > <a href="admin.php?type=page&action=read">Page List</a><br /><br />';
	
		$pageList = $this->loadArr(new page($this->conn, $this->log), "created:DESC");
		
		if (count($pageList)) {
			foreach($pageList as $page) {
				echo "
				<div class=\"page\">
					<h2>
					<a href=\"admin.php?type=page&action=update&p=". $page->getId() ."\" " . ($page->getIsHome()==1 ? "id='cms_homepageMarker'":"") . " title='" . ($page->getIsHome()==1 ? "Edit / Manage the homepage":"Edit / Manage this page") . "' class=\"cms_pageEditLink\" >" . $page->getTitle() . "</a>
					</h2>
					<p>" . SITE_ROOT . $page->getSafeLink() . "</p>
				</div>";
			}
		} else {
			echo "<p>No pages found!</p>";
		}
	}
}

?>

